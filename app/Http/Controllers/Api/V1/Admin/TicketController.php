<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(protected TicketService $tickets) {}

    /**
     * Validasi tiket via hasil scan QR.
     * Menerima kode tiket mentah maupun payload QR ("JLK:{code}").
     */
    public function validate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
        ]);

        $code = trim($data['code']);

        if (str_starts_with($code, 'JLK:')) {
            $code = substr($code, 4);
        }

        $result = $this->tickets->validate($code, $request->user());

        $ticket = $result['ticket'];

        return response()->json([
            'result' => $result['result'],
            'valid' => $result['result'] === 'valid',
            'message' => $result['message'],
            'ticket' => $ticket ? [
                'code' => $ticket->code,
                'type' => $ticket->ticket_type_name,
                'visit_date' => $ticket->visit_date->toDateString(),
                'status' => $ticket->status,
                'used_at' => $ticket->used_at?->toIso8601String(),
                'destination' => $ticket->booking?->destination?->name,
                'booking_code' => $ticket->booking?->code,
            ] : null,
        ], $result['result'] === 'invalid' ? 404 : 200);
    }
}
