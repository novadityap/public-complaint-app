<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Response extends Model
{
  use HasUuids;
  protected $keyType = 'string';
  public $incrementing = false;
  protected $guarded = [];

  public function complaint(): BelongsTo
  {
    return $this->belongsTo(Complaint::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
