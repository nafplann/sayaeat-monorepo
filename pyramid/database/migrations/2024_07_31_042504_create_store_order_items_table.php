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
        Schema::create('store_order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('remark')->nullable();
            $table->integer('quantity');
            $table->double('price');
            $table->double('markup_amount')->default(0);
            $table->double('total');
            $table->ulid('order_id');
            $table->ulid('product_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_order_items');
    }
};
