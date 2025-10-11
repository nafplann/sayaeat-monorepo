<?php

namespace App\Notifications\MarketAja;

use App\Models\StoreOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Webhook\WebhookChannel;
use NotificationChannels\Webhook\WebhookMessage;

class NewOrderReceived extends Notification implements ShouldQueue
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

        $itemsMarkup = $order->items->reduce(fn($carry, $item) => $carry + $item->markup_amount, 0);
        $totalItems = $order->items->reduce(fn($carry, $item) => $carry + $item->quantity, 0);
        $subtotal = display_price($order->subtotal - $itemsMarkup);

        $this->subject = 'Hai mitra, ada pesanan baru!';
        $this->body = "{$totalItems} item senilai {$subtotal}";
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
        $orderUrl = url("/manage/store-orders");

        return WebhookMessage::create()
            ->query([
                'message' => "$this->subject%0a$this->body%0aCek pesanan di $orderUrl",
            ]);
    }
}
