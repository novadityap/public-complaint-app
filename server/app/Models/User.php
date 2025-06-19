<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasUuids, HasApiTokens;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $guarded = [];
  protected $keyType = 'string';
  public $incrementing = false;

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

  public function role(): BelongsTo
  {
    return $this->belongsTo(Role::class);
  }

  public function complaints(): HasMany {
    return $this->hasMany(Complaint::class);
  }

  public function responses(): HasMany {
    return $this->hasMany(Response::class);
  }

  public function refreshTokens(): HasMany {
    return $this->hasMany(RefreshToken::class);
  }
}
