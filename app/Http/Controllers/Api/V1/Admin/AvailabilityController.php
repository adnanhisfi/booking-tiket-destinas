<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'ticket_type_id' => ['nullable', 'integer', 'exists:ticket_types,id'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $rows = TicketAvailability::query()
            ->with('ticketType:id,name,destination_id,default_quota')
            ->when($data['ticket_type_id'] ?? null, fn ($q, $id) => $q->where('ticket_type_id', $id))
            ->when($data['destination_id'] ?? null, fn ($q, $id) => $q->whereHas(
                'ticketType',
                fn ($t) => $t->where('destination_id', $id)
            ))
            ->when($data['from'] ?? null, fn ($q, $from) => $q->whereDate('visit_date', '>=', $from))
            ->when($data['to'] ?? null, fn ($q, $to) => $q->whereDate('visit_date', '<=', $to))
            ->orderBy('visit_date')
            ->paginate((int) $request->integer('per_page', 30));

        return response()->json([
            'data' => collect($rows->items())->map(fn (TicketAvailability $a) => [
                'id' => $a->id,
                'ticket_type_id' => $a->ticket_type_id,
                'ticket_type' => $a->ticketType?->name,
                'visit_date' => $a->visit_date->toDateString(),
                'quota' => $a->quota,
                'sold' => $a->sold,
                'remaining' => $a->remaining(),
            ]),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    /**
     * Atur kuota (default dan override) per tanggal.
     */
    public function upsert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'items.*.visit_date' => ['required', 'date_format:Y-m-d'],
            'items.*.quota' => ['required', 'integer', 'min:0'],
        ]);

        $result = DB::transaction(function () use ($data) {
            $saved = [];

            foreach ($data['items'] as $item) {
                $type = TicketType::findOrFail($item['ticket_type_id']);
                $date = Carbon::parse($item['visit_date'])->toDateString();

                $row = TicketAvailability::where('ticket_type_id', $type->id)
                    ->whereDate('visit_date', $date)
                    ->lockForUpdate()
                    ->first();

                if ($row) {
                    if ($item['quota'] < $row->sold) {
                        throw ValidationException::withMessages([
                            'items' => "Kuota {$type->name} pada {$date} tidak boleh lebih kecil dari jumlah terjual ({$row->sold}).",
                        ]);
                    }

                    $row->update(['quota' => $item['quota']]);
                } else {
                    $row = TicketAvailability::create([
                        'ticket_type_id' => $type->id,
                        'visit_date' => $date,
                        'quota' => $item['quota'],
                        'sold' => 0,
                    ]);
                }

                $saved[] = [
                    'ticket_type_id' => $row->ticket_type_id,
                    'visit_date' => $row->visit_date->toDateString(),
                    'quota' => $row->quota,
                    'sold' => $row->sold,
                ];
            }

            return $saved;
        });

        return response()->json(['message' => 'Kuota diperbarui.', 'data' => $result]);
    }
}
