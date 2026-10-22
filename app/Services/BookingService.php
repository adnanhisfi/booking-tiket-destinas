<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(protected QuotaService $quota) {}

    /**
     * Buat booking pending: kuota ditahan sementara, harga disimpan sebagai snapshot.
     *
     * @param  array{destination_id:int, visit_date:string, items:array<int, array{ticket_type_id:int, quantity:int}>, voucher_code?:string|null, customer_name?:string|null, customer_phone?:string|null}  $data
     *
     * @throws ValidationException
     */
    public function create(User $user, array $data): Booking
    {
        $visitDate = Carbon::parse($data['visit_date'])->startOfDay();

        $this->assertVisitDateAllowed($visitDate);

        return DB::transaction(function () use ($user, $data, $visitDate) {
            $destination = Destination::query()
                ->active()
                ->lockForUpdate()
                ->find($data['destination_id']);

            if (! $destination) {
                throw ValidationException::withMessages([
                    'destination_id' => 'Destinasi tidak ditemukan atau sedang tidak aktif.',
                ]);
            }

            $booking = new Booking;
            $booking->code = $this->generateCode();
            $booking->user_id = $user->id;
            $booking->destination_id = $destination->id;
            $booking->visit_date = $visitDate;
            $booking->customer_name = $data['customer_name'] ?? $user->name;
            $booking->customer_phone = $data['customer_phone'] ?? ($user->phone ?? '-');
            $booking->status = Booking::STATUS_PENDING;
            $booking->subtotal = 0;
            $booking->discount_amount = 0;
            $booking->total_amount = 0;
            $booking->expires_at = now()->addMinutes((int) config('payment.booking_ttl_minutes'));
            $booking->save();

            $subtotal = 0.0;

            foreach ($data['items'] as $item) {
                $quantity = (int) $item['quantity'];

                if ($quantity < 1) {
                    throw ValidationException::withMessages([
                        'items' => 'Jumlah tiket minimal 1.',
                    ]);
                }

                $ticketType = TicketType::query()
                    ->active()
                    ->where('destination_id', $destination->id)
                    ->find($item['ticket_type_id']);

                if (! $ticketType) {
                    throw ValidationException::withMessages([
                        'items' => 'Jenis tiket tidak valid untuk destinasi ini.',
                    ]);
                }

                $this->quota->reserve($ticketType, $visitDate, $quantity);

                $lineTotal = round((float) $ticketType->price * $quantity, 2);
                $subtotal += $lineTotal;

                BookingItem::create([
                    'booking_id' => $booking->id,
                    'ticket_type_id' => $ticketType->id,
                    'ticket_type_name' => $ticketType->name,
                    'unit_price' => $ticketType->price,
                    'quantity' => $quantity,
                    'subtotal' => $lineTotal,
                ]);
            }

            $discount = 0.0;
            $voucher = $this->resolveVoucher($data['voucher_code'] ?? null, $destination->id, $subtotal, $booking);

            if ($voucher) {
                $discount = $voucher->discountFor($subtotal);
                $voucher->increment('used');
            }

            $booking->update([
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => max(0, round($subtotal - $discount, 2)),
                'voucher_id' => $voucher?->id,
                'voucher_code' => $voucher?->code,
            ]);

            return $booking->load(['items.ticketType', 'destination']);
        });
    }

    /**
     * Batalkan booking pending dan kembalikan kuota.
     */
    public function cancel(Booking $booking, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status !== Booking::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'booking' => 'Hanya booking yang menunggu pembayaran yang bisa dibatalkan.',
                ]);
            }

            $this->releaseQuota($booking);

            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'notes' => $reason,
            ]);

            $booking->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->update(['status' => Payment::STATUS_EXPIRED]);

            return $booking;
        });
    }

    /**
     * Worker: kedaluwarsakan booking pending yang melewati batas bayar dan kembalikan kuota.
     */
    public function expireDueBookings(): int
    {
        $count = 0;

        Booking::query()
            ->where('status', Booking::STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use (&$count) {
                foreach ($bookings as $booking) {
                    if ($this->expire($booking)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function expire(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking) {
            $locked = Booking::query()->lockForUpdate()->find($booking->id);

            if (! $locked || $locked->status !== Booking::STATUS_PENDING) {
                return false;
            }

            if ($locked->expires_at->isFuture()) {
                return false;
            }

            $this->releaseQuota($locked);

            $locked->update(['status' => Booking::STATUS_EXPIRED]);

            $locked->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->update(['status' => Payment::STATUS_EXPIRED]);

            return true;
        });
    }

    protected function releaseQuota(Booking $booking): void
    {
        foreach ($booking->items()->get() as $item) {
            $this->quota->release($item->ticket_type_id, $booking->visit_date, $item->quantity);
        }
    }

    protected function resolveVoucher(?string $code, int $destinationId, float $amount, Booking $booking): ?Voucher
    {
        if (! $code) {
            return null;
        }

        $voucher = Voucher::query()
            ->where('code', Str::upper(trim($code)))
            ->lockForUpdate()
            ->first();

        if (! $voucher) {
            throw ValidationException::withMessages(['voucher_code' => 'Kode voucher tidak ditemukan.']);
        }

        if ($message = $voucher->isApplicable($destinationId, $amount)) {
            throw ValidationException::withMessages(['voucher_code' => $message]);
        }

        return $voucher;
    }

    protected function assertVisitDateAllowed(Carbon $visitDate): void
    {
        if ($visitDate->lt(today())) {
            throw ValidationException::withMessages([
                'visit_date' => 'Tanggal kunjungan tidak boleh di masa lalu.',
            ]);
        }

        $maxDays = (int) config('payment.max_advance_days');

        if ($visitDate->gt(today()->addDays($maxDays))) {
            throw ValidationException::withMessages([
                'visit_date' => "Tanggal kunjungan maksimal {$maxDays} hari ke depan.",
            ]);
        }
    }

    protected function generateCode(): string
    {
        $prefix = 'JLK-'.now()->format('Ymd').'-';
        $lastCode = Booking::query()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('code');

        $sequence = $lastCode ? ((int) substr($lastCode, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
