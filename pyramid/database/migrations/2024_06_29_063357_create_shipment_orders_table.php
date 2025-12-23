<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipment_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('order_number');
            $table->double('delivery_fee');
            $table->double('service_fee');
            $table->double('total');
            $table->double('distance');
            $table->tinyInteger('status');
            $table->text('status_text')->nullable();
            $table->tinyInteger('payment_status');
            $table->text('payment_status_text')->nullable();
            $table->tinyInteger('payment_method');
            $table->ulid('customer_id');
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('sender_address');
            $table->double('sender_latitude');
            $table->double('sender_longitude');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('item_details');
            $table->double('item_weight');
            $table->ulid('driver_id')->nullable();
            $table->text('item_image_path')->nullable();
            $table->text('payment_proof_path')->nullable();
            $table->ulid('payment_confirmed_by')->nullable();
            $table->string('note_to_driver')->nullable();
            $table->string('canceled_from')->nullable();
            $table->string('canceled_reason')->nullable();
            $table->ulid('canceled_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_orders');
    }
};
