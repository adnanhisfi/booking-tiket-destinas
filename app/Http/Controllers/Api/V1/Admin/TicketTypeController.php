<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ticketTypes = TicketType::query()
            ->with('destination:id,name')
            ->when($request->filled('destination_id'), fn ($q) => $q->where('destination_id', $request->integer('destination_id')))
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'data' => collect($ticketTypes->items())->map(fn (TicketType $t) => $this->payload($t)),
            'meta' => [
                'current_page' => $ticketTypes->currentPage(),
                'last_page' => $ticketTypes->lastPage(),
                'total' => $ticketTypes->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $ticketType = TicketType::create($data);

        return response()->json([
            'message' => 'Jenis tiket dibuat.',
            'data' => $this->payload($ticketType->load('destination')),
        ], 201);
    }

    public function update(Request $request, TicketType $ticketType): JsonResponse
    {
        $ticketType->update($this->validated($request, $ticketType));

        return response()->json([
            'message' => 'Jenis tiket diperbarui. Pesanan lama tetap memakai harga snapshot.',
            'data' => $this->payload($ticketType->refresh()->load('destination')),
        ]);
    }

    public function destroy(TicketType $ticketType): JsonResponse
    {
        $ticketType->update(['status' => TicketType::STATUS_INACTIVE]);

        return response()->json(['message' => 'Jenis tiket dinonaktifkan.']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?TicketType $ticketType = null): array
    {
        return $request->validate([
            'destination_id' => [$ticketType ? 'sometimes' : 'required', 'integer', 'exists:destinations,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'default_quota' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in([TicketType::STATUS_ACTIVE, TicketType::STATUS_INACTIVE])],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(TicketType $ticketType): array
    {
        return [
            'id' => $ticketType->id,
            'destination_id' => $ticketType->destination_id,
            'destination' => $ticketType->destination?->name,
            'name' => $ticketType->name,
            'description' => $ticketType->description,
            'price' => (float) $ticketType->price,
            'default_quota' => $ticketType->default_quota,
            'status' => $ticketType->status,
        ];
    }
}
