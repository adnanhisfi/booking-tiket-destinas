<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->with('booking:id,code,customer_name,total_amount')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->boolean('awaiting_verification'), fn ($q) => $q
                ->where('status', Payment::STATUS_PENDING)
                ->whereNotNull('proof_path'))
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'data' => collect($payments->items())->map(fn (Payment $p) => [
                'id' => $p->id,
                'transaction_ref' => $p->transaction_ref,
                'booking_code' => $p->booking?->code,
                'customer' => $p->booking?->customer_name,
                'amount' => (float) $p->amount,
                'method' => $p->method,
                'status' => $p->status,
                'has_proof' => (bool) $p->proof_path,
                'proof_url' => $p->proof_path ? url('storage/'.$p->proof_path) : null,
                'verified_at' => $p->verified_at?->toIso8601String(),
                'created_at' => $p->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * Verifikasi bukti transfer manual.
     */
    public function verify(Request $request, Payment $payment): JsonResponse
    {
        $payment = $this->payments->verifyManual($payment, $request->user());

        return response()->json([
            'message' => 'Pembayaran diverifikasi, e-tiket diterbitkan.',
            'data' => [
                'transaction_ref' => $payment->transaction_ref,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ],
        ]);
    }
}
