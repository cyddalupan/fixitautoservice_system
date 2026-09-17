<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public ?Customer $customer = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $customerName = $this->customer
            ? trim(($this->customer->first_name ?? '') . ' ' . ($this->customer->last_name ?? ''))
            : 'Website guest';

        return [
            'appointment_id' => $this->appointment->id,
            'appointment_number' => $this->appointment->appointment_number,
            'appointment_date' => $this->appointment->appointment_date,
            'appointment_time' => $this->appointment->appointment_time,
            'customer_name' => $customerName,
            'message' => 'New booking ' . $this->appointment->appointment_number . ' from ' . $customerName,
            'link' => '/appointments',
        ];
    }
}
