<?php

namespace App\Observers;

use App\Enums\MakanAjaOrderStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderPaymentStatus;
use App\Models\Driver;
use App\Models\Order;
use App\Models\User;
use App\Notifications\DriverRating;
use App\Notifications\MakanAja\NewOrderReceived;
use App\Notifications\MakanAja\OrderCompleted;
use App\Notifications\MakanAja\OrderConfirmedByMerchant;
use App\Notifications\MakanAja\OrderIsOnDelivery;
use App\Notifications\MakanAja\OrderPaid;
use App\Notifications\MakanAja\OrderPaymentReceived;
use App\Notifications\MakanAja\OrderRejected;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Order "saved" event.
     */
    public function saved(Order $order): void
    {
        $merchant = $order->merchant;

        if ($order->wasRecentlyCreated) {
            if ($merchant && $order->payment_method === OrderPaymentMethod::CASH_ON_DELIVERY->value) {
                $merchant->notify(new NewOrderReceived($order));
            }
        }

        $admins = User::role(['admin'])->get();
        $changes = $order->getChanges();

        if (array_key_exists('status', $changes)) {
            $status = $changes['status'];

            // Notify admin to verify payment
            if ($status == MakanAjaOrderStatus::WAITING_FOR_PAYMENT_VERIFICATION->value) {
                foreach ($admins as $admin) {
                    $admin->notify(new OrderPaid($order));
                }
            }

            // Notify merchant to confirm order
            if ($merchant && $status == MakanAjaOrderStatus::WAITING_FOR_MERCHANT_CONFIRMATION->value) {
                $merchant->notify(new NewOrderReceived($order));
            }

            // Notify admin to set driver
            if ($status == MakanAjaOrderStatus::SEARCHING_FOR_DRIVER->value) {
                foreach ($admins as $admin) {
                    $admin->notify(new OrderConfirmedByMerchant($order));
                }
            }

            // Notify customer that order is on delivery
            if ($status == MakanAjaOrderStatus::ON_DELIVERY->value) {
                $order->customer->notify(new OrderIsOnDelivery($order));
            }

            // Notify customer that order is completed
            if ($status == MakanAjaOrderStatus::COMPLETED->value) {
                $order->customer->notify(new OrderCompleted($order));
            }

            // Notify customer that order is canceled
            if ($status == MakanAjaOrderStatus::CANCELED->value && $order->canceled_from === 'MERCHANT') {
                $order->customer->notify(new OrderRejected($order));
            }
        }

        if (array_key_exists('payment_status', $changes)) {
            $paymentStatus = $changes['payment_status'];

            // Notify customer that payment has been received
            if ($paymentStatus == OrderPaymentStatus::PAYMENT_RECEIVED->value) {
                $order->customer->notify(new OrderPaymentReceived($order));
            }
        }

        if (array_key_exists('is_rated', $changes)) {
            $isRated = (int)$changes['is_rated'];

            // Notify driver regarding rating
            if ($isRated) {
                $rating = $order->ratings()
                    ->where('model_type', Driver::class)
                    ->first();

                $order->driver->notify(new DriverRating($rating));
            }
        }
    }
}
