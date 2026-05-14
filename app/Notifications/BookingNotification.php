<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class BookingNotification extends Notification
{
    use Queueable;

    public $booking;
    public $type;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking, $type, $message)
    {
        $this->booking = $booking;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'type' => $this->type,
            'message' => $this->message,
            'tour_name' => $this->booking->tourSchedule->tour->name,
            'user_name' => $this->booking->user->name,
            'created_at' => now(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'booking_id' => $this->booking->id,
            'type' => $this->type,
            'message' => $this->message,
            'tour_name' => $this->booking->tourSchedule->tour->name,
            'user_name' => $this->booking->user->name,
            'created_at' => now(),
        ]);
    }
}
