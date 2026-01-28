<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TicketEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $eventName;
    private $name;
    private $amount;
    private $ticketList;
    private $basePath;

    /**
     * Create a new message instance.
     */
    public function __construct(string $eventName, string $name, int $amount, Collection $ticketList)
    {
        $this->eventName = $eventName;
        $this->name = $name;
        $this->amount = $amount;
        $this->ticketList = $ticketList;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->eventName);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $content = new Content(
            view: 'ticket.email',
            with: [
                'eventName' => $this->eventName,
                'name' => $this->name,
                'amount' => $this->amount,
                'coverImage' => asset('storage/uploads/Cover-Tiket.png'),
                'ticketList' => $this->ticketList,
            ],
        );

        return $content;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
