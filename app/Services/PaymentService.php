<?php

namespace App\Services;

use App\Jobs\SendEticketJob;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\GatewayManager;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        protected GatewayManager $gateways,
        protected TicketService $tickets,
    ) {}

    /**
     * Inisiasi pembayaran untuk booking yang masih pending.
     *
     * @return array{payment: Payment, instructions: array<string, mixed>}
     */
    public function initiate(Booking $booking, string $method): array
    {
        if (! $booking->isPending()) {
            throw ValidationException::withMessages([
                'booking' => 'Booking tidak dalam status menunggu pembayaran.',
            ]);
        }

        if ($booking->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'booking' => 'Batas waktu pembayaran sudah lewat.',
            ]);
        }

        $gateway = $this->gateways->driver();

        $existing = $booking->payments()->where('status', Payment::STATUS_PENDING)->first();

        if ($existing) {
            return [
                'payment' => $existing,
                'instructions' => $existing->gateway_payload['instructions'] ?? [],
            ];
        }

        $transaction = $gateway->createTransaction($booking, $method);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_ref' => $transaction['transaction_ref'],
            'gateway_order_id' => $transaction['gateway_order_id'],
            'method' => $method,
            'amount' => $booking->total_amount,
            'status' => Payment::STATUS_PENDING,
            'gateway_payload' => [
                'driver' => $gateway->name(),
                'redirect_url' => $transaction['redirect_url'],
                'instructions' => $transaction['instructions'],
            ],
        ]);

        return ['payment' => $payment, 'instructions' => $transaction['instructions']];
    }

    /**
     * Simpan bukti transfer manual.
     */
    public function attachProof(Payment $payment, UploadedFile $file): Payment
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran ini sudah tidak bisa diunggah buktinya.',
            ]);
        }

        $path = $file->store('payment-proofs', 'public');

        $payment->update(['proof_path' => $path]);

        return $payment->refresh();
    }

    /**
     * Proses webhook gateway secara idempotent.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): ?Payment
    {
        $gateway = $this->gateways->driver();
        $normalized = $gateway->parseWebhook($payload);

        if ($normalized['transaction_ref'] === '') {
            throw ValidationException::withMessages(['transaction_ref' => 'transaction_ref wajib ada.']);
        }

        return DB::transaction(function () use ($normalized, $payload) {
            $payment = Payment::query()
                ->where('transaction_ref', $normalized['transaction_ref'])
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                Log::warning('Webhook untuk transaksi tidak dikenal', $normalized);

                return null;
            }

            if ($payment->isPaid()) {
                Log::info('Webhook duplikat diabaikan', ['transaction_ref' => $payment->transaction_ref]);

                return $payment;
            }

            $payment->gateway_payload = array_merge($payment->gateway_payload ?? [], [
                'last_webhook' => $payload,
                'last_webhook_at' => now()->toIso8601String(),
            ]);

            if ($normalized['status'] === Payment::STATUS_PAID) {
                $payment->save();

                return $this->markPaid($payment, $normalized);
            }

            $payment->status = $normalized['status'];
            $payment->save();

            return $payment;
        });
    }

    /**
     * Verifikasi manual oleh admin (bukti transfer).
     */
    public function verifyManual(Payment $payment, User $admin): Payment
    {
        return DB::transaction(function () use ($payment, $admin) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->isPaid()) {
                return $payment;
            }

            if (! $payment->proof_path) {
                throw ValidationException::withMessages([
                    'payment' => 'Bukti transfer belum diunggah pengunjung.',
                ]);
            }

            $payment->verified_at = now();
            $payment->verified_by = $admin->id;
            $payment->save();

            return $this->markPaid($payment, [
                'status' => Payment::STATUS_PAID,
                'method' => $payment->method,
                'paid_at' => now()->toIso8601String(),
                'gateway_order_id' => $payment->gateway_order_id,
                'transaction_ref' => $payment->transaction_ref,
            ]);
        });
    }

    /**
     * Tandai pembayaran lunas, lunasi booking, dan terbitkan e-tiket.
     *
     * @param  array<string, mixed>  $normalized
     */
    protected function markPaid(Payment $payment, array $normalized): Payment
    {
        $payment->status = Payment::STATUS_PAID;
        $payment->paid_at = isset($normalized['paid_at']) && $normalized['paid_at']
            ? Carbon::parse($normalized['paid_at'])
            : now();
        $payment->method = $normalized['method'] ?? $payment->method;
        $payment->gateway_order_id = $normalized['gateway_order_id'] ?? $payment->gateway_order_id;
        $payment->save();

        $booking = Booking::query()->lockForUpdate()->find($payment->booking_id);

        if (! $booking) {
            return $payment->refresh();
        }

        if ($booking->status === Booking::STATUS_PENDING) {
            $booking->update([
                'status' => Booking::STATUS_PAID,
                'paid_at' => $payment->paid_at,
            ]);

            $this->tickets->issue($booking->fresh());

            SendEticketJob::dispatch($booking->id);
        } elseif (in_array($booking->status, [Booking::STATUS_EXPIRED, Booking::STATUS_CANCELLED], true)) {
            Log::warning('Pembayaran diterima setelah booking kedaluwarsa; perlu rekonsiliasi', [
                'booking_code' => $booking->code,
                'transaction_ref' => $payment->transaction_ref,
            ]);
        }

        return $payment->refresh();
    }
}
