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
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->longText('details')->nullable();
            $table->double('price');
            $table->string('unit');
            $table->tinyInteger('status');
            $table->string('status_text')->nullable();
            $table->ulid('store_id');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('condition')->nullable();
            $table->integer('sorting')->default(0);
            $table->integer('minimum_purchase_quantity')->default(1);
            $table->text('image_path')->nullable();
            $table->boolean('prescription_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
