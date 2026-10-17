<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. إضافة حقول الاسترجاع والشيفت لجدول invoices
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'status')) {
                $table->string('status', 32)->default('paid')->after('type'); // paid, refunded, cancelled
            }
            if (!Schema::hasColumn('invoices', 'refund_reason')) {
                $table->text('refund_reason')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('invoices', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_reason');
            }
            if (!Schema::hasColumn('invoices', 'refunded_by')) {
                $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('invoices', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->after('created_by')->constrained('shifts')->onDelete('set null');
            }
        });

        // 2. تحديث enum الخاص بـ shift_actions ليشمل عمليات الاسترجاع
        try {
            DB::statement("ALTER TABLE shift_actions MODIFY COLUMN action_type ENUM('new_subscription','renew_subscription','end_session','separate_sale','add_booking','end_booking','expense_note','refund','invoice_refund') NOT NULL");
        } catch (\Throwable $e) {
            // في حال وجود بيئة sqlite أثناء الاختبارات
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'refunded_by')) {
                $table->dropForeign(['refunded_by']);
                $table->dropColumn('refunded_by');
            }
            if (Schema::hasColumn('invoices', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }
            if (Schema::hasColumn('invoices', 'refunded_at')) {
                $table->dropColumn('refunded_at');
            }
            if (Schema::hasColumn('invoices', 'refund_reason')) {
                $table->dropColumn('refund_reason');
            }
            if (Schema::hasColumn('invoices', 'status')) {
                $table->dropColumn('status');
            }
        });

        try {
            DB::statement("ALTER TABLE shift_actions MODIFY COLUMN action_type ENUM('new_subscription','renew_subscription','end_session','separate_sale','add_booking','end_booking','expense_note') NOT NULL");
        } catch (\Throwable $e) {
        }
    }
};
