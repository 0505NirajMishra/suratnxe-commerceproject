<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 10;

    public function __construct(
        public array $orderDetails,
        public array $items,
        public bool $storeCopy = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Order '.$this->orderDetails['number'].' received');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-placed',
            with: [
                'orderDetails' => $this->orderDetails,
                'items' => $this->items,
                'storeCopy' => $this->storeCopy,
            ],
        );
    }

    public function middleware(): array
    {
        return [(new RateLimited('mail'))->releaseAfter(2)];
    }
}
