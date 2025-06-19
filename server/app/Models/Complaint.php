<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
  use HasUuids;
  protected $keyType = 'string';
  public $incrementing = false;
  protected $guarded = [];

  protected $casts = [
    'images' => 'array',
  ];

  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  public function responses(): HasMany
  {
    return $this->hasMany(Response::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
