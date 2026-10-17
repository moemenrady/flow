<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'type',
        'quantity',
        'balance_after',
        'invoice_id',
        'user_id',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * المادة الخام
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * الفاتورة المرتبطة بالحركة (إن وجدت)
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * الموظف أو المستخدم الذي تسبب في الحركة
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * مسميات أنواع الحركات بالعربية
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sale'        => 'خصم بيع',
            'sale_cancel' => 'استرجاع بيع',
            'sale_update' => 'تسوية تعديل بيع',
            'restock'     => 'توريد / شراء',
            'waste'       => 'هالك / تالف',
            default       => $this->type,
        };
    }
}
