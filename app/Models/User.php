<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'email_verified_at',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function invoice(): HasMany
  {
    return $this->hasMany(Invoice::class, 'client_id', 'id');
  }

  public function shifts(): HasMany
  {
    return $this->hasMany(Shift::class);
  }

  /**
   * الشيفت المفتوح والنشط حالياً للمستخدم
   */
  public function activeShift(): HasOne
  {
    return $this->hasOne(Shift::class)->whereNull('end_time')->latestOfMany();
  }

  public function hasRole($role): bool
  {
    return $this->role === $role;
  }

  /**
   * هل المستخدم يمتلك صلاحيات إدارية (أدمن أو مشرف أو مدير)
   */
  public function isManager(): bool
  {
    return in_array(strtolower((string) $this->role), ['admin', 'supervisor', 'manager', 'ادمن', 'مدير'], true);
  }

  public function isSupervisor(): bool
  {
    return strtolower((string) $this->role) === 'supervisor';
  }
}
