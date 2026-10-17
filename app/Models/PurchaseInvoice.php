<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'supplier_name',
        'invoice_date',
        'total_amount',
        'discount',
        'tax',
        'net_amount',
        'paid_amount',
        'remaining_amount',
        'payment_method',
        'payment_status',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'invoice_date'     => 'date',
        'total_amount'     => 'decimal:2',
        'discount'         => 'decimal:2',
        'tax'              => 'decimal:2',
        'net_amount'       => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    /**
     * أصناف وبنود فاتورة الشراء
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'purchase_invoice_id');
    }

    /**
     * المستخدم / المدير الذي حرر الفاتورة
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * توليد رقم فاتورة شراء تسلسلي فريد
     */
    public static function generateNextInvoiceNumber(): string
    {
        $datePrefix = 'PUR-' . Carbon::today()->format('Ymd') . '-';
        $latest = static::where('invoice_number', 'LIKE', $datePrefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->invoice_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return $datePrefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * نص طريقة الدفع بالعربية
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match (strtolower((string) $this->payment_method)) {
            'cash'          => 'كاش نقدي',
            'instapay'      => 'إنستا باي',
            'card', 'visa'  => 'فيزا / بطاقة',
            'credit'        => 'آجل (حساب مورد)',
            'bank_transfer' => 'تحويل بنكي',
            default         => $this->payment_method ?: 'كاش نقدي',
        };
    }

    /**
     * نص ولون شارة حالة السداد
     */
    public function getPaymentStatusInfoAttribute(): array
    {
        return match (strtolower((string) $this->payment_status)) {
            'paid' => [
                'label' => 'مدفوعة بالكامل',
                'color' => 'success',
                'badge' => 'bg-success',
            ],
            'partial' => [
                'label' => 'مدفوعة جزئياً',
                'color' => 'warning',
                'badge' => 'bg-warning text-dark',
            ],
            'unpaid' => [
                'label' => 'آجلة (غير مدفوعة)',
                'color' => 'danger',
                'badge' => 'bg-danger',
            ],
            default => [
                'label' => 'مدفوعة',
                'color' => 'success',
                'badge' => 'bg-success',
            ],
        };
    }
}
