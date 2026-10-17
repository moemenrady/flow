<?php
// app/Models/Invoice.php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
  protected $fillable = [
    'request_id',
    'invoice_number',
    'client_id',
    'booking_id',
    'created_by',
    'shift_id',
    'type',
    'status',
    'cycle_number',
    'subtotal',
    'discount_type',
    'discount_value',
    'discount_amount',
    'discount_reason',
    'discount_by',
    'total',
    'profit',
    'notes',
    'refund_reason',
    'refunded_at',
    'refunded_by',
  ];

  protected $casts = [
    'subtotal'        => 'decimal:2',
    'discount_value'  => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'total'           => 'decimal:2',
    'profit'          => 'decimal:2',
    'refunded_at'     => 'datetime',
  ];

  protected $guarded = [];

  public function items(): HasMany
  {
    return $this->hasMany(InvoiceItem::class);
  }

  public function booking(): BelongsTo
  {
    return $this->belongsTo(Booking::class, 'booking_id');
  }

  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'client_id');
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function refunder(): BelongsTo
  {
    return $this->belongsTo(User::class, 'refunded_by');
  }

  public function shift(): BelongsTo
  {
    return $this->belongsTo(Shift::class, 'shift_id');
  }

  public function deposits(): HasMany
  {
    return $this->hasMany(BookingDeposit::class);
  }

  public function inventoryMovements(): HasMany
  {
    return $this->hasMany(InventoryMovement::class, 'invoice_id');
  }

  public function shiftActions(): HasMany
  {
    return $this->hasMany(ShiftAction::class, 'invoice_id');
  }

  public function scopeBetween($query, $from, $to)
  {
    return $query->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);
  }

  /**
   * هل الفاتورة مرتجعة بالفعل؟
   */
  public function isRefunded(): bool
  {
    return $this->status === 'refunded';
  }

  /**
   * هل الفاتورة مؤهلة للاسترجاع؟
   */
  public function canBeRefunded(): bool
  {
    return $this->status !== 'refunded' && $this->status !== 'cancelled';
  }
}
