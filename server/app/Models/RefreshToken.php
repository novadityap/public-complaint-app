<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
  use HasUuids;
  protected $keyType = 'string';
  public $incrementing = false;
  protected $guarded = [];
  public $timestamps = false;

  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
  }
}
