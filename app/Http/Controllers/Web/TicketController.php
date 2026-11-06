<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * E-ticket berisi QR code untuk setiap tiket pada booking lunas.
     */
    public function show(Request $request, Booking $booking, TicketService $tickets): View
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);

        abort_unless(in_array($booking->status, [Booking::STATUS_PAID, Booking::STATUS_COMPLETED], true), 404);

        $booking->load(['destination:id,name,location']);

        $bookingTickets = $booking->tickets()->orderBy('id')->get();

        if ($bookingTickets->isEmpty()) {
            $bookingTickets = collect($tickets->issue($booking));
        }

        $qrs = $bookingTickets->mapWithKeys(fn (Ticket $ticket) => [
            $ticket->id => $tickets->qrSvg($ticket),
        ]);

        return view('pengunjung.booking.eticket', compact('booking', 'bookingTickets', 'qrs'));
    }

    /**
     * Unduh QR code satu tiket sebagai berkas SVG.
     */
    public function qr(Request $request, Booking $booking, Ticket $ticket, TicketService $tickets): Response
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);
        abort_unless((int) $ticket->booking_id === (int) $booking->id, 404);
        abort_unless(in_array($booking->status, [Booking::STATUS_PAID, Booking::STATUS_COMPLETED], true), 404);

        return response($tickets->qrSvg($ticket), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="e-ticket-'.$ticket->code.'.svg"',
        ]);
    }
}
