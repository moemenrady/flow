<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 8, 2);
            $table->unsignedInteger('quantity',)->default(0);
            $table->unsignedInteger('min_quantity',)->default(0);
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_produced')->default(0);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade'); // لو حذفت صنف، كل المنتجات المرتبطة بيه تتحذف

            $table->timestamps();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
