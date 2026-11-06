<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with('booking:id,code,customer_name,total_amount,status')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->boolean('awaiting_verification'), fn ($q) => $q
                ->where('status', Payment::STATUS_PENDING)
                ->whereNotNull('proof_path'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q')->toString();

                $q->where(function ($sub) use ($term) {
                    $sub->where('transaction_ref', 'like', "%{$term}%")
                        ->orWhereHas('booking', function ($booking) use ($term) {
                            $booking->where('code', 'like', "%{$term}%")
                                ->orWhere('customer_name', 'like', "%{$term}%");
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $counts = Payment::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.pembayaran.index', [
            'payments' => $payments,
            'counts' => $counts,
            'awaitingVerification' => Payment::query()
                ->where('status', Payment::STATUS_PENDING)
                ->whereNotNull('proof_path')
                ->count(),
        ]);
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $this->payments->verifyManual($payment, $request->user());

        return back()->with('ok', 'Pembayaran diverifikasi dan e-tiket diterbitkan.');
    }
}
