<?php

namespace Noorfarooqy\EasyNotifications\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EasyNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $subject;
    public $view_template;
    public $email_body;
    public $attachments;
    public function __construct($email_body, $subject = 'Easy Notification Mail', $view_template = 'en::mail.easy_notification_template', $attachments = [])
    {
        if (env('APP_DEBUG')) {
            Log::info('[*] Sending email notification');
        }

        $this->email_body = $email_body;
        $this->subject = $subject;
        $this->view_template = $view_template;
        $this->attachments = $attachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: $this->view_template,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attached_files = [];
        // foreach ($this->attachments as $attachment) {
        //     $attached_files[] = Attachment::fromPath($attachment);
        // }
        return $attached_files;
    }
}
