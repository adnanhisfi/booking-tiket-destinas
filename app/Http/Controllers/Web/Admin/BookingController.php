<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Services\BookingService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookings,
        protected ReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        $bookings = $this->query($request)
            ->with(['user:id,name,email', 'destination:id,name'])
            ->paginate(15)
            ->withQueryString();

        $counts = Booking::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.booking.index', [
            'bookings' => $bookings,
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
            'counts' => $counts,
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'user:id,name,email,phone',
            'destination:id,name,slug,location',
            'items',
            'tickets',
            'payments' => fn ($q) => $q->latest('id'),
        ]);

        return view('admin.booking.show', compact('booking'));
    }

    public function complete(Booking $booking): RedirectResponse
    {
        if ($booking->status !== Booking::STATUS_PAID) {
            return back()->withErrors(['booking' => 'Hanya booking berstatus lunas yang dapat diselesaikan.']);
        }

        $booking->update(['status' => Booking::STATUS_COMPLETED]);

        return back()->with('ok', 'Booking ditandai selesai.');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->status !== Booking::STATUS_PENDING) {
            return back()->withErrors(['booking' => 'Hanya booking menunggu pembayaran yang dapat dibatalkan.']);
        }

        $this->bookings->cancel($booking, 'Dibatalkan oleh admin.');

        return back()->with('ok', 'Booking dibatalkan dan kuota tiket dikembalikan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->reports->bookingRows($request->only(['from', 'to', 'destination_id', 'status']));

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'laporan-booking-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function query(Request $request)
    {
        return Booking::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('destination_id'), fn ($q) => $q->where('destination_id', $request->integer('destination_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('visit_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('visit_date', '<=', $request->input('to')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q')->toString();
                $q->where(function ($sub) use ($term) {
                    $sub->where('code', 'like', "%{$term}%")
                        ->orWhere('customer_name', 'like', "%{$term}%")
                        ->orWhere('customer_phone', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('id');
    }
}
