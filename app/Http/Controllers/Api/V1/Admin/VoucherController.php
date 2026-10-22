<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vouchers = Voucher::query()
            ->with('destination:id,name')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'data' => collect($vouchers->items())->map(fn (Voucher $v) => $this->payload($v)),
            'meta' => [
                'current_page' => $vouchers->currentPage(),
                'last_page' => $vouchers->lastPage(),
                'total' => $vouchers->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['code'] = Str::upper($data['code']);

        $voucher = Voucher::create($data);

        return response()->json([
            'message' => 'Voucher dibuat.',
            'data' => $this->payload($voucher->load('destination')),
        ], 201);
    }

    public function update(Request $request, Voucher $voucher): JsonResponse
    {
        $data = $this->validated($request, $voucher);

        if (isset($data['code'])) {
            $data['code'] = Str::upper($data['code']);
        }

        $voucher->update($data);

        return response()->json([
            'message' => 'Voucher diperbarui.',
            'data' => $this->payload($voucher->refresh()->load('destination')),
        ]);
    }

    public function destroy(Voucher $voucher): JsonResponse
    {
        $voucher->update(['status' => 'inactive']);

        return response()->json(['message' => 'Voucher dinonaktifkan.']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Voucher $voucher = null): array
    {
        return $request->validate([
            'code' => [$voucher ? 'sometimes' : 'required', 'string', 'max:50', Rule::unique('vouchers', 'code')->ignore($voucher?->id)],
            'type' => ['required', Rule::in([Voucher::TYPE_PERCENT, Voucher::TYPE_FIXED])],
            'value' => ['required', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'quota' => ['required', 'integer', 'min:0'],
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Voucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'type' => $voucher->type,
            'value' => (float) $voucher->value,
            'min_amount' => (float) $voucher->min_amount,
            'max_discount' => $voucher->max_discount !== null ? (float) $voucher->max_discount : null,
            'quota' => $voucher->quota,
            'used' => $voucher->used,
            'destination' => $voucher->destination?->name,
            'start_at' => $voucher->start_at?->toIso8601String(),
            'end_at' => $voucher->end_at?->toIso8601String(),
            'status' => $voucher->status,
        ];
    }
}
