<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
  use HasUuids;
  protected $keyType = 'string';
  public $incrementing = false;
  protected $guarded = [];

  public function complaints(): HasMany
  {
    return $this->hasMany(Complaint::class);
  }
}
