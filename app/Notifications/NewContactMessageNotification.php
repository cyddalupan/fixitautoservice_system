<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ContactMessage $contactMessage,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'contact_message_id' => $this->contactMessage->id,
            'name'               => $this->contactMessage->name,
            'email'              => $this->contactMessage->email,
            'subject'            => $this->contactMessage->subject,
            'message'            => 'New contact message from ' . $this->contactMessage->name,
            'link'               => '/inbox',
        ];
    }
}
