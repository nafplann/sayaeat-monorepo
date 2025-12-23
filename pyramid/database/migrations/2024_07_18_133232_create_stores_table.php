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
        Schema::create('stores', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('category');
            $table->text('address');
            $table->string('phone_number');
            $table->string('primary_whatsapp_number')->nullable();
            $table->string('secondary_whatsapp_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('qris_link')->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->text('logo_path')->nullable();
            $table->tinyInteger('status');
            $table->string('status_text')->nullable();
            $table->ulid('owner_id')->nullable();
            $table->json('operating_hours')->nullable();
            $table->tinyInteger('enable_product_markup')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
