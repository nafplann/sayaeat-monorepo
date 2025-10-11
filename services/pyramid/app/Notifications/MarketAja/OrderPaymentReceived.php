<?php

namespace App\Notifications\MarketAja;

use App\Models\StoreOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Webhook\WebhookChannel;
use NotificationChannels\Webhook\WebhookMessage;

class OrderPaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private StoreOrder $order;
    private string $subject;
    private string $body;

    /**
     * Create a new notification instance.
     */
    public function __construct(StoreOrder $order)
    {
        $this->order = $order;
        $customerName = $order->customer->name;

        $this->subject = "Hai $customerName, pembayaran kamu diterima!";
        $this->body = 'Pesanan kamu akan segera diproses.';
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
        $orderUrl = env('HAPI_URL') . "/orders/market-aja/{$this->order->id}";

        return WebhookMessage::create()
            ->query([
                'message' => "$this->subject%0a$this->body%0aCek pesanan kamu di $orderUrl",
            ]);
    }
}
