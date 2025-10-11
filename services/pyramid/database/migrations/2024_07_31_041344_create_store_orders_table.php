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
        Schema::create('store_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('order_number');
            $table->double('delivery_fee');
            $table->double('service_fee');
            $table->double('subtotal');
            $table->double('total');
            $table->double('distance');
            $table->tinyInteger('status');
            $table->text('status_text')->nullable();
            $table->tinyInteger('payment_status');
            $table->tinyInteger('payment_method');
            $table->text('payment_status_text')->nullable();
            $table->string('address_label');
            $table->string('address_detail');
            $table->double('address_latitude');
            $table->double('address_longitude');
            $table->text('payment_proof_path')->nullable();
            $table->string('note_to_driver')->nullable();
            $table->string('canceled_from')->nullable();
            $table->string('canceled_reason')->nullable();
            $table->ulid('canceled_by')->nullable();
            $table->string('store_paid_by')->nullable();
            $table->ulid('payment_confirmed_by')->nullable();
            $table->ulid('customer_id');
            $table->ulid('store_id');
            $table->ulid('driver_id')->nullable();
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
        Schema::dropIfExists('store_orders');
    }
};
