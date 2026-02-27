<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
  public $incrementing = false;
  protected $keyType = 'int';

  protected $fillable = [
    'id',
    'name',
    'price',
    'cost',
    'quantity',
    'min_quantity',
  ];

  public function purchases()
  {
    return $this->hasMany(SessionPurchase::class);
  }

  public function important_products()
  {
    return $this->hasMany(ImportantProduct::class);
  }
}
