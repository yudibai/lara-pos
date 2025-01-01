<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('owner_id')->nullable();
            $table->tinyInteger('product_category_id');
            $table->string('name', 40)->nullable();
            $table->integer('price')->nullable();
            $table->string('image_product')->nullable();
            $table->String('sku', 15)->nullable();
            $table->String('plu', 15)->nullable();
            $table->integer('capital')->nullable();
            $table->text('description')->nullable();
            $table->text('active_by_placeid')->nullable();
            $table->tinyInteger('active')->nullable();
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
