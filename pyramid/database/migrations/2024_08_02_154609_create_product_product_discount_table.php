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
        Schema::create('product_product_discount', function (Blueprint $table) {
            $table->foreignUlid('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreignUlid('product_discount_id')
                ->references('id')
                ->on('product_discounts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_product_discount');
    }
};
