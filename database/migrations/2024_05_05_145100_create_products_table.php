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
            $table->string('name_product');
            $table->string('slug_product');
            $table->string('code_product');
            $table->string('qr_code_product');
            $table->text('description_product');
            $table->string('image_product')->nullable();
            $table->decimal('initial_stock', 20, 2);
            $table->decimal('adjustment', 20, 2);
            $table->decimal('final_stock', 20, 2);
            $table->decimal('stock_alert', 20, 2);
            $table->decimal('cost_price', 20, 2);
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('warehouse_id')->constrained('warehouses');

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
