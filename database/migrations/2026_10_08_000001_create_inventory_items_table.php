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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique(); // كود الخامة التسلسلي
            $table->string('name')->unique(); // اسم المادة الخام (بن، لبن، سكر، أكواب..)
            $table->string('category')->nullable()->default('خامات ومشروبات'); // تصنيف الخامة
            $table->decimal('quantity', 10, 2)->default(0.00); // الرصيد الحالي المتوفر
            $table->string('unit')->default('قطعة'); // الوحدة (جرام gm، مل ml، قطعة pcs..)
            $table->decimal('reorder_level', 10, 2)->default(0.00); // حد إعادة الطلب والنواقص
            $table->decimal('unit_price', 10, 2)->default(0.00); // سعر تكلفة الوحدة
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('quantity');
            $table->index('unit_price');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
