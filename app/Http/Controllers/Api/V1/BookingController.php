<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookings) {}

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookings->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Reservasi dibuat. Selesaikan pembayaran sebelum batas waktu.',
            'data' => $this->payload($booking),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::query()
            ->with(['destination:id,name,location,thumbnail'])
            ->where('user_id', $request->user()->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 10));

        return response()->json([
            'data' => collect($bookings->items())->map(fn (Booking $b) => $this->payload($b)),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeOwner($request, $booking);

        $booking->load([
            'destination:id,name,location,thumbnail',
            'items',
            'tickets',
            'payments' => fn ($q) => $q->orderByDesc('id'),
        ]);

        return response()->json(['data' => $this->payload($booking, true)]);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeOwner($request, $booking);

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $booking = $this->bookings->cancel($booking, $data['reason'] ?? null);

        return response()->json([
            'message' => 'Booking dibatalkan dan kuota dikembalikan.',
            'data' => $this->payload($booking),
        ]);
    }

    protected function authorizeOwner(Request $request, Booking $booking): void
    {
        if ($booking->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke booking ini.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Booking $booking, bool $detailed = false): array
    {
        $payload = [
            'id' => $booking->id,
            'code' => $booking->code,
            'status' => $booking->status,
            'visit_date' => $booking->visit_date?->toDateString(),
            'subtotal' => (float) $booking->subtotal,
            'discount_amount' => (float) $booking->discount_amount,
            'total_amount' => (float) $booking->total_amount,
            'voucher_code' => $booking->voucher_code,
            'expires_at' => $booking->expires_at?->toIso8601String(),
            'paid_at' => $booking->paid_at?->toIso8601String(),
            'created_at' => $booking->created_at?->toIso8601String(),
            'destination' => $booking->destination ? [
                'id' => $booking->destination->id,
                'name' => $booking->destination->name,
                'location' => $booking->destination->location,
                'thumbnail' => $booking->destination->thumbnail,
            ] : null,
        ];

        if ($booking->relationLoaded('items')) {
            $payload['items'] = $booking->items->map(fn ($item) => [
                'ticket_type_id' => $item->ticket_type_id,
                'name' => $item->ticket_type_name,
                'unit_price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ]);
        }

        if ($detailed) {
            $payload['customer'] = [
                'name' => $booking->customer_name,
                'phone' => $booking->customer_phone,
            ];

            if ($booking->relationLoaded('tickets')) {
                $payload['tickets'] = $booking->tickets->map(fn ($ticket) => [
                    'id' => $ticket->id,
                    'code' => $ticket->code,
                    'type' => $ticket->ticket_type_name,
                    'status' => $ticket->status,
                    'used_at' => $ticket->used_at?->toIso8601String(),
                ]);
            }

            if ($booking->relationLoaded('payments')) {
                $payload['payments'] = $booking->payments->map(fn ($payment) => [
                    'transaction_ref' => $payment->transaction_ref,
                    'method' => $payment->method,
                    'amount' => (float) $payment->amount,
                    'status' => $payment->status,
                    'has_proof' => (bool) $payment->proof_path,
                    'paid_at' => $payment->paid_at?->toIso8601String(),
                ]);
            }
        }

        return $payload;
    }
}
