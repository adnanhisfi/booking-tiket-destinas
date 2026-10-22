<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class EticketMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, Ticket>  $tickets
     */
    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E-Tiket '.$this->booking->destination->name.' - '.$this->booking->code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.eticket',
            with: [
                'booking' => $this->booking,
                'tickets' => $this->booking->tickets,
            ],
        );
    }
}
