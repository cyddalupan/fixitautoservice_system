<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ContactMessage;

/**
 * Notifies admin when a new Contact Us submission arrives on the public
 * website. Recipients come from config('mail.admin_recipients')
 * (per ADMIN NAV DECISION: cydmdalupan@gmail.com + andrewacecontreras@gmail.com).
 */
class NewContactAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    public function __construct(ContactMessage $contact)
    {
        $this->contact = $contact;
    }

    public function build(): self
    {
        return $this
            ->subject('New Contact Message: ' . ($this->contact->subject ?: 'No subject'))
            ->view('emails.new-contact-admin-notification');
    }
}
