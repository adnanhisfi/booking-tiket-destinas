<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    public function __construct(protected TicketService $tickets) {}

    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->with('booking.destination:id,name,location,thumbnail')
            ->where('user_id', $request->user()->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('upcoming'), fn ($q) => $q->whereDate('visit_date', '>=', now()->toDateString()))
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json([
            'data' => collect($tickets->items())->map(fn (Ticket $t) => $this->payload($t)),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorizeAccess($request, $ticket);

        $ticket->load('booking.destination:id,name,location');

        return response()->json(['data' => $this->payload($ticket)]);
    }

    /**
     * QR code tiket dalam format SVG (dapat diunduh / dicetak).
     */
    public function qr(Request $request, Ticket $ticket): Response
    {
        $this->authorizeAccess($request, $ticket);

        $svg = $this->tickets->qrSvg($ticket);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="tiket-'.$ticket->code.'.svg"',
        ]);
    }

    protected function authorizeAccess(Request $request, Ticket $ticket): void
    {
        if ($ticket->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'type' => $ticket->ticket_type_name,
            'status' => $ticket->status,
            'visit_date' => $ticket->visit_date?->toDateString(),
            'used_at' => $ticket->used_at?->toIso8601String(),
            'qr_payload' => $this->tickets->qrPayload($ticket),
            'qr_url' => url("/api/v1/tickets/{$ticket->code}/qr"),
            'booking' => $ticket->booking ? [
                'code' => $ticket->booking->code,
                'destination' => $ticket->booking->destination?->name,
                'location' => $ticket->booking->destination?->location,
                'thumbnail' => $ticket->booking->destination?->thumbnail,
            ] : null,
        ];
    }
}
