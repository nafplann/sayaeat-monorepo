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
        Schema::create('shipment_order_destinations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('address');
            $table->double('latitude');
            $table->double('longitude');
            $table->foreignUlid('shipment_order_id')
                ->references('id')
                ->on('shipment_orders');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_order_destinations');
    }
};
