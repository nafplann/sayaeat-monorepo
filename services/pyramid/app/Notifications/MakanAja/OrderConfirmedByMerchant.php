<?php

namespace App\Notifications\MakanAja;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Webhook\WebhookChannel;
use NotificationChannels\Webhook\WebhookMessage;

class OrderConfirmedByMerchant extends Notification implements ShouldQueue
{
    use Queueable;

    private Order $order;
    private string $subject;
    private string $body;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $merchantName = $order->merchant->name;

        $this->subject = "Hai admin, pesanan diterima merchant ($merchantName)!";
        $this->body = 'Silahkan menugaskan driver.';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebhookChannel::class, 'database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'subject' => $this->subject,
            'body' => $this->body,
        ];
    }

    public function toWebhook($notifiable)
    {
        $orderUrl = url("/manage/makan-aja");

        return WebhookMessage::create()
            ->query([
                'message' => "$this->subject%0a$this->body%0aCek pesanan di $orderUrl",
            ]);
    }
}
