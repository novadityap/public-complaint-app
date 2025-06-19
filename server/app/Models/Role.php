<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
  use HasUuids;
  protected $keyType = 'string';
  public $incrementing = false;
  protected $guarded = [];

  public function user(): HasMany
  {
    return $this->hasMany(User::class);
  }
}
