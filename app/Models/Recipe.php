<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'inventory_item_id',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:2',
    ];

    protected $appends = [
        'ingredient_cost',
    ];

    /**
     * المنتج المباع التابع له الوصفة
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * الاسم المستعار للتوافق
     */
    public function menuItem(): BelongsTo
    {
        return $this->product();
    }

    /**
     * المادة الخام المستهلكة
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * تكلفة هذا المكون (الكمية المستهلكة × سعر تكلفة وحدة الخامة)
     */
    public function getIngredientCostAttribute(): float
    {
        if (!$this->inventoryItem) {
            return 0.0;
        }

        return round((float) $this->quantity_used * (float) ($this->inventoryItem->unit_price ?? 0), 2);
    }
}
