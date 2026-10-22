<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TicketService
{
    /**
     * Terbitkan e-tiket untuk booking yang sudah lunas. Idempotent:
     * pemanggilan ulang tidak menghasilkan tiket ganda.
     *
     * @return array<int, Ticket>
     */
    public function issue(Booking $booking): array
    {
        return DB::transaction(function () use ($booking) {
            $booking->refresh();

            if ($booking->tickets()->exists()) {
                return $booking->tickets()->get()->all();
            }

            if (! in_array($booking->status, [Booking::STATUS_PAID, Booking::STATUS_COMPLETED], true)) {
                throw new RuntimeException('Tiket hanya dapat diterbitkan untuk booking yang sudah dibayar.');
            }

            $tickets = [];

            foreach ($booking->items()->with('ticketType')->get() as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $tickets[] = Ticket::create([
                        'booking_id' => $booking->id,
                        'booking_item_id' => $item->id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'user_id' => $booking->user_id,
                        'visit_date' => $booking->visit_date->toDateString(),
                        'code' => $this->generateCode(),
                        'ticket_type_name' => $item->ticket_type_name,
                        'unit_price' => $item->unit_price,
                        'status' => Ticket::STATUS_VALID,
                    ]);
                }
            }

            return $tickets;
        });
    }

    /**
     * Validasi tiket di pintu masuk.
     *
     * @return array{result: string, message: string, ticket: Ticket|null}
     */
    public function validate(string $code, User $officer): array
    {
        return DB::transaction(function () use ($code, $officer) {
            $ticket = Ticket::with('booking.destination')
                ->where('code', $code)
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                return $this->failure('invalid', 'Tiket tidak valid atau tidak ditemukan.');
            }

            if ($ticket->status === Ticket::STATUS_VOID) {
                return $this->failure('void', 'Tiket sudah dibatalkan.', $ticket);
            }

            if ($ticket->status === Ticket::STATUS_USED) {
                return $this->failure(
                    'already_used',
                    'Tiket sudah dipakai pada '.$ticket->used_at?->toDateTimeString().'.',
                    $ticket
                );
            }

            if ($ticket->visit_date->toDateString() !== now()->toDateString()) {
                return $this->failure(
                    'wrong_date',
                    'Tiket hanya berlaku pada tanggal kunjungan '.$ticket->visit_date->toDateString().'.',
                    $ticket
                );
            }

            if (! in_array($ticket->booking->status, [Booking::STATUS_PAID, Booking::STATUS_COMPLETED], true)) {
                return $this->failure('not_paid', 'Pembayaran booking belum lunas.', $ticket);
            }

            $ticket->update([
                'status' => Ticket::STATUS_USED,
                'used_at' => now(),
                'validated_by' => $officer->id,
            ]);

            return [
                'result' => 'valid',
                'message' => 'Tiket valid. Silakan masuk.',
                'ticket' => $ticket->fresh(),
            ];
        });
    }

    /**
     * Payload yang ditanam di QR: token acak milik tiket.
     */
    public function qrPayload(Ticket $ticket): string
    {
        return 'JLK:'.$ticket->code;
    }

    public function qrSvg(Ticket $ticket, int $scale = 5): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'svgAddXmlHeader' => false,
            'outputBase64' => false,
            'scale' => $scale,
        ]);

        return (new QRCode($options))->render($this->qrPayload($ticket));
    }

    public function generateCode(): string
    {
        return Str::upper(bin2hex(random_bytes(16)));
    }

    /**
     * @return array{result: string, message: string, ticket: Ticket|null}
     */
    protected function failure(string $result, string $message, ?Ticket $ticket = null): array
    {
        return ['result' => $result, 'message' => $message, 'ticket' => $ticket];
    }
}
