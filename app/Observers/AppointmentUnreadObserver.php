<?php

namespace App\Observers;

use App\Models\Appointment;

class AppointmentUnreadObserver
{
    /**
     * When an appointment is updated, mark it as unread (reset viewed_at)
     * so it reappears in the red badge.
     * Skip if it's just the viewed_at being set (that's a read, not an update).
     */
    public function updated(Appointment $appointment): void
    {
        if ($appointment->wasChanged() && !$appointment->wasChanged('viewed_at') && $appointment->viewed_at !== null) {
            $appointment->update(['viewed_at' => null]);
        }
    }
}
