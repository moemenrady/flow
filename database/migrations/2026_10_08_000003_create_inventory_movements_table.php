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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            // أنواع الحركات: بيع، تعديل بيع، إلغاء بيع/استرجاع، توريد شراء، هالك وتالف
            $table->enum('type', ['sale', 'sale_update', 'sale_cancel', 'restock', 'waste']);
            // الكمية (موجبة للتوريد والاسترجاع، سالبة للبيع والهالك)
            $table->decimal('quantity', 10, 2);
            // الرصيد الفعلي بعد الحركة
            $table->decimal('balance_after', 10, 2);
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['inventory_item_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
