<?php

namespace App\Notifications\Horus;

use App\Models\Driver;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class NewOrderOffer extends Notification implements ShouldQueue
{
    use Queueable;

    private Order $order;
    private Driver $driver;

    private string $subject;
    private string $body;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, Driver $driver)
    {
        $this->order = $order;
        $this->driver = $driver;

        $driverName = $driver->name;

        $distance = $order->distance;
        $deliveryFee = display_price($order->delivery_fee);
        $this->subject = "Hai $driverName, ada pesanan baru!";
        $this->body = "Pesanan Makan-Aja dengan jarak $distance km, ongkos kirim Rp $deliveryFee";

        Log::info('going to send notification to driver: ' . $driver->name);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        Log::info('NewOrderOffer toFcm: ' . $this->body);

        return (new FcmMessage())
            ->data([
                'type' => 'new_order',
                'order_type' => 'makan_aja',
                'order' => json_encode($this->order->toArray()),
                'notifee' => json_encode([
                    'title' => $this->subject,
                    'body' => $this->body,
                    'android' => [
                        'channelId' => 'default',
                        'actions' => [
                            [
                                'title' => 'Terima',
                                'pressAction' => ['id' => 'accept'],
                            ],
                            [
                                'title' => 'Tolak',
                                'pressAction' => ['id' => 'reject'],
                            ],
                        ]
                    ],
                ]),
            ]);
    }
}
