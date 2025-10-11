<?php

namespace App\Notifications;

use App\Models\Rating;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Webhook\WebhookChannel;
use NotificationChannels\Webhook\WebhookMessage;

class DriverRating extends Notification implements ShouldQueue
{
    use Queueable;

    private Rating $rating;

    /**
     * Create a new notification instance.
     */
    public function __construct(Rating $rating)
    {
        $this->rating = $rating;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebhookChannel::class];
    }

    public function toWebhook($notifiable)
    {
        $driver = $this->rating->model;
        $review = $this->rating->review;
        $star = str_repeat('⭐', ceil($this->rating->rating));

        return WebhookMessage::create()
            ->query([
                'message' => "Hai {$driver->name}, kamu mendapat review dari customer.%0aRating: $star%0aReview: $review"
            ]);
    }
}
