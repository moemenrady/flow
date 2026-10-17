<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
  public $incrementing = false;
  protected $keyType = 'int';

  protected static function booted()
  {
    static::creating(function ($product) {
      if (empty($product->id)) {
        $product->id = ((int) static::max('id')) + 1;
      }
      if ($product->cost === null) {
        $product->cost = 0;
      }
      if ($product->quantity === null) {
        $product->quantity = 0;
      }
      if ($product->min_quantity === null) {
        $product->min_quantity = 0;
      }
    });
  }

  protected $attributes = [
    'cost' => 0,
    'quantity' => 0,
    'min_quantity' => 0,
    'is_produced' => 0,
  ];

  protected $fillable = [
    'id',
    'name',
    'price',
    'cost',
    'quantity',
    'category_id',
    'min_quantity',
    'is_produced',
  ];

  protected $casts = [
    'price' => 'decimal:2',
    'cost'  => 'decimal:2',
  ];

  public function purchases(): HasMany
  {
    return $this->hasMany(SessionPurchase::class);
  }

  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  public function important_products(): HasMany
  {
    return $this->hasMany(ImportantProduct::class);
  }

  /**
   * الوصفات والمكونات الخام التي تدخل في إعداد هذا المنتج
   */
  public function recipes(): HasMany
  {
    return $this->hasMany(Recipe::class, 'product_id');
  }

  /**
   * التحقق مما إذا كان المنتج له وصفة مسجلة
   */
  public function hasRecipe(): bool
  {
    return $this->recipes()->exists();
  }

  /**
   * حساب تكلفة المنتج التلقائية بناءً على مكونات الوصفة وسعر خامات المخزن
   */
  public function getRecipeCostAttribute(): float
  {
    return round((float) $this->recipes->sum(function ($recipe) {
      return (float) $recipe->quantity_used * (float) ($recipe->inventoryItem->unit_price ?? 0);
    }), 2);
  }

  /**
   * التكلفة الفعلية المعتمدة (إما من الوصفة أو التكلفة الثابتة المسجلة)
   */
  public function getActualCostAttribute(): float
  {
    if ($this->hasRecipe()) {
      return $this->recipe_cost;
    }

    return (float) ($this->cost ?? 0);
  }

  /**
   * هامش الربح المحسوب تلقائياً
   */
  public function getProfitMarginAttribute(): float
  {
    $cost = $this->actual_cost;
    $price = (float) $this->price;

    if ($price <= 0) {
      return 0.0;
    }

    return round((($price - $cost) / $price) * 100, 1);
  }
}
