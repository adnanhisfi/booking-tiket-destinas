<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(Request $request): JsonResponse
    {
        $bookings = $this->query($request)
            ->with(['user:id,name,email', 'destination:id,name'])
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'data' => collect($bookings->items())->map(fn (Booking $b) => [
                'id' => $b->id,
                'code' => $b->code,
                'customer' => $b->customer_name,
                'email' => $b->user?->email,
                'destination' => $b->destination?->name,
                'visit_date' => $b->visit_date->toDateString(),
                'total_amount' => (float) $b->total_amount,
                'status' => $b->status,
                'created_at' => $b->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load(['user:id,name,email', 'destination:id,name', 'items', 'tickets', 'payments']);

        return response()->json([
            'data' => [
                'code' => $booking->code,
                'status' => $booking->status,
                'customer' => [
                    'name' => $booking->customer_name,
                    'phone' => $booking->customer_phone,
                    'email' => $booking->user?->email,
                ],
                'destination' => $booking->destination?->name,
                'visit_date' => $booking->visit_date->toDateString(),
                'subtotal' => (float) $booking->subtotal,
                'discount_amount' => (float) $booking->discount_amount,
                'total_amount' => (float) $booking->total_amount,
                'expires_at' => $booking->expires_at?->toIso8601String(),
                'paid_at' => $booking->paid_at?->toIso8601String(),
                'items' => $booking->items->map(fn ($i) => [
                    'name' => $i->ticket_type_name,
                    'quantity' => $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                    'subtotal' => (float) $i->subtotal,
                ]),
                'tickets' => $booking->tickets->map(fn ($t) => [
                    'code' => $t->code,
                    'status' => $t->status,
                    'used_at' => $t->used_at?->toIso8601String(),
                ]),
                'payments' => $booking->payments->map(fn ($p) => [
                    'transaction_ref' => $p->transaction_ref,
                    'method' => $p->method,
                    'amount' => (float) $p->amount,
                    'status' => $p->status,
                    'has_proof' => (bool) $p->proof_path,
                    'proof_url' => $p->proof_path ? url('storage/'.$p->proof_path) : null,
                    'paid_at' => $p->paid_at?->toIso8601String(),
                ]),
            ],
        ]);
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
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('destination_id'), fn ($q) => $q->where('destination_id', $request->integer('destination_id')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('visit_date', $request->string('date')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($sub) use ($request) {
                $term = $request->string('q')->toString();
                $sub->where('code', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%");
            }))
            ->orderByDesc('id');
    }
}
