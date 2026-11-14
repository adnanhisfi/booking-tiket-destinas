<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookings) {}

    /**
     * Form pemesanan tiket untuk satu destinasi.
     */
    public function create(Request $request, Destination $destination): View
    {
        abort_if($destination->status !== Destination::STATUS_ACTIVE, 404);

        $maxDate = today()->addDays((int) config('payment.max_advance_days'));
        $visitDate = $this->parseVisitDate($request->input('tanggal'));

        if ($visitDate->lt(today()) || $visitDate->gt($maxDate)) {
            $visitDate = today();
        }

        $ticketTypes = $destination->ticketTypes()
            ->where('status', TicketType::STATUS_ACTIVE)
            ->orderBy('price')
            ->get();

        $rows = TicketAvailability::query()
            ->whereIn('ticket_type_id', $ticketTypes->pluck('id'))
            ->whereDate('visit_date', $visitDate->toDateString())
            ->get()
            ->keyBy('ticket_type_id');

        $availability = $ticketTypes->mapWithKeys(fn (TicketType $type) => [
            $type->id => $rows->has($type->id) ? $rows[$type->id]->remaining() : $type->default_quota,
        ]);

        return view('pengunjung.booking.create', compact(
            'destination',
            'ticketTypes',
            'visitDate',
            'availability',
            'maxDate',
        ));
    }

    /**
     * Simpan reservasi lalu arahkan ke halaman checkout/pembayaran.
     */
    public function store(StoreBookingRequest $request, Destination $destination): RedirectResponse
    {
        $data = $request->validated();
        $data['destination_id'] = $destination->id;

        if (($data['items'] ?? []) === []) {
            throw ValidationException::withMessages([
                'items' => 'Pilih minimal satu tiket sebelum melanjutkan.',
            ]);
        }

        $booking = $this->bookings->create($request->user(), $data);

        return redirect()
            ->route('booking.show', ['booking' => $booking->code])
            ->with('ok', 'Booking berhasil dibuat. Selesaikan pembayaran sebelum '
                .$booking->expires_at->translatedFormat('d M Y H:i').'.');
    }

    /**
     * Halaman checkout / rincian booking milik pengunjung.
     */
    public function show(Request $request, Booking $booking): View
    {
        $this->authorizeOwner($request, $booking);

        if ($booking->isExpiredByTime()) {
            $this->bookings->expire($booking);
            $booking->refresh();
        }

        $booking->load([
            'user:id,name,email',
            'destination:id,name,slug,location,thumbnail',
            'items',
            'payments' => fn ($q) => $q->latest('id'),
            'tickets',
        ]);

        return view('pengunjung.booking.checkout', [
            'booking' => $booking,
            'methodOptions' => config('payment.methods'),
        ]);
    }

    /**
     * Riwayat booking dengan tab aktif / selesai / dibatalkan.
     */
    public function history(Request $request): View
    {
        $tab = $request->input('tab', 'aktif');

        $query = $request->user()->bookings()
            ->with(['destination:id,name,location,thumbnail', 'items'])
            ->latest('id');

        match ($tab) {
            'selesai' => $query->where('status', Booking::STATUS_COMPLETED),
            'batal' => $query->whereIn('status', [Booking::STATUS_EXPIRED, Booking::STATUS_CANCELLED]),
            default => $query->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_PAID]),
        };

        $bookings = $query->paginate(10)->withQueryString();

        return view('pengunjung.riwayat', compact('bookings', 'tab'));
    }

    /**
     * Halaman status setelah bukti pembayaran diunggah.
     */
    public function status(Request $request, Booking $booking): View
    {
        $this->authorizeOwner($request, $booking);

        $booking->load(['destination:id,name,location', 'payments' => fn ($q) => $q->latest('id')]);

        return view('pengunjung.booking.status', [
            'booking' => $booking,
            'payment' => $booking->payments->first(),
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeOwner($request, $booking);

        $this->bookings->cancel($booking);

        return redirect()
            ->route('booking.show', ['booking' => $booking->code])
            ->with('ok', 'Booking berhasil dibatalkan dan kuota tiket dikembalikan.');
    }

    /**
     * Parse tanggal kunjungan dari query string secara aman.
     * Input tidak valid dikembalikan ke hari ini alih-alih memicu error 500.
     */
    protected function parseVisitDate(mixed $value): Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return today();
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);

            return $date instanceof Carbon ? $date->startOfDay() : today();
        } catch (\Throwable) {
            return today();
        }
    }

    protected function authorizeOwner(Request $request, Booking $booking): void
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);
    }
}
