<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** An editable template from the panel, sent as text and branded HTML. */
class TemplateMail extends Mailable
{
    use Queueable;

    /** @param array{address: string, name: string} $from */
    public function __construct(
        public string $subjectLine,
        public string $bodyText,
        public array $fromAddress,
        public ?string $replyToAddress = null,
        public string $locale_ = 'es',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress['address'], $this->fromAddress['name']),
            replyTo: $this->replyToAddress ? [new Address($this->replyToAddress)] : [],
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.template',
            text: 'mail.template-text',
            with: ['body' => $this->bodyText, 'lang' => $this->locale_],
        );
    }
}
