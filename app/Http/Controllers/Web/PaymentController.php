<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePaymentProofRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    /**
     * Buat instruksi pembayaran untuk booking yang masih pending.
     */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeOwner($request, $booking);

        $data = $request->validate([
            'method' => ['required', 'string', 'in:'.implode(',', array_keys(config('payment.methods')))],
        ], [
            'method.required' => 'Pilih metode pembayaran terlebih dahulu.',
            'method.in' => 'Metode pembayaran tidak tersedia.',
        ]);

        $result = $this->payments->initiate($booking, $data['method']);

        return redirect()
            ->route('payment.show', ['payment' => $result['payment']->transaction_ref])
            ->with('ok', 'Instruksi pembayaran dibuat. Selesaikan sebelum batas waktu.');
    }

    /**
     * Tampilkan instruksi pembayaran dan form unggah bukti transfer.
     */
    public function show(Request $request, Payment $payment): View
    {
        $payment->load('booking.destination');

        abort_unless((int) $payment->booking->user_id === (int) $request->user()->id, 403);

        return view('pengunjung.booking.pembayaran', [
            'payment' => $payment,
            'booking' => $payment->booking,
            'instructions' => $payment->gateway_payload['instructions'] ?? [],
            'driver' => $payment->gateway_payload['driver'] ?? config('payment.driver'),
        ]);
    }

    /**
     * Unggah bukti transfer manual.
     */
    public function proof(StorePaymentProofRequest $request, Payment $payment): RedirectResponse
    {
        abort_unless((int) $payment->booking->user_id === (int) $request->user()->id, 403);

        $this->payments->attachProof($payment, $request->file('proof'));

        return redirect()
            ->route('booking.status', ['booking' => $payment->booking->code])
            ->with('ok', 'Bukti transfer berhasil diunggah. Pembayaran menunggu verifikasi admin.');
    }

    protected function authorizeOwner(Request $request, Booking $booking): void
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);
    }
}
