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
        Schema::create('coupons', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->integer('max_per_customer')->default(1);
            $table->integer('total_quantity')->default(1);
            $table->integer('redeemed_quantity')->default(0);
            $table->double('minimum_purchase')->nullable();
            $table->double('discount_amount')->nullable();
            $table->double('max_discount_amount')->nullable();
            $table->double('discount_percentage')->nullable();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->boolean('is_platform_promotion');
            $table->boolean('is_enabled');
            $table->ulid('merchant_id')->nullable();
            $table->string('merchant_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
