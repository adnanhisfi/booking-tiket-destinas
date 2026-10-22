<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePaymentProofRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payments\GatewayManager;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $payments,
        protected GatewayManager $gateways,
    ) {}

    /**
     * Inisiasi pembayaran untuk booking milik pengguna.
     */
    public function store(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke booking ini.');
        }

        $data = $request->validate([
            'method' => ['required', 'string', 'in:'.implode(',', array_keys(config('payment.methods')))],
        ]);

        $result = $this->payments->initiate($booking, $data['method']);

        return response()->json([
            'message' => 'Instruksi pembayaran dibuat.',
            'data' => [
                'transaction_ref' => $result['payment']->transaction_ref,
                'method' => $result['payment']->method,
                'amount' => (float) $result['payment']->amount,
                'status' => $result['payment']->status,
                'expires_at' => $booking->expires_at->toIso8601String(),
                'instructions' => $result['instructions'],
            ],
        ], 201);
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        $payment->load('booking');

        if ($payment->booking->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke pembayaran ini.');
        }

        return response()->json(['data' => $this->payload($payment)]);
    }

    /**
     * Unggah bukti transfer (driver manual).
     */
    public function proof(StorePaymentProofRequest $request, Payment $payment): JsonResponse
    {
        $payment->load('booking');

        if ($payment->booking->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke pembayaran ini.');
        }

        $payment = $this->payments->attachProof($payment, $request->file('proof'));

        return response()->json([
            'message' => 'Bukti transfer diterima, menunggu verifikasi admin.',
            'data' => $this->payload($payment),
        ]);
    }

    /**
     * Webhook dari payment gateway. Idempotent terhadap pengiriman ganda.
     */
    public function webhook(Request $request): JsonResponse
    {
        $gateway = $this->gateways->driver();

        if (! $gateway->verifySignature($request->getContent(), $request->headers->all())) {
            return response()->json(['message' => 'Signature tidak valid.'], 401);
        }

        $payload = $request->json()->all() ?: $request->all();

        $payment = $this->payments->handleWebhook($payload);

        if (! $payment) {
            return response()->json(['message' => 'Transaksi tidak dikenal.'], 202);
        }

        return response()->json([
            'message' => 'Webhook diproses.',
            'data' => $this->payload($payment->refresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Payment $payment): array
    {
        return [
            'transaction_ref' => $payment->transaction_ref,
            'booking_code' => $payment->booking?->code,
            'method' => $payment->method,
            'amount' => (float) $payment->amount,
            'status' => $payment->status,
            'has_proof' => (bool) $payment->proof_path,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ];
    }
}
