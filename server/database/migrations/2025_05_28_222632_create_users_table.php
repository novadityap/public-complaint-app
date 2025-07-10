<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('username')->unique();
      $table->string('email')->unique();
      $table->string('avatar')->default(config('app.default_avatar_url'));
      $table->string('password')->nullable();
      $table->boolean('is_verified')->default(false);
      $table->string('verification_token')->nullable();
      $table->timestamp('verification_token_expires')->nullable();
      $table->string('reset_token')->nullable();
      $table->timestamp('reset_token_expires')->nullable();
      $table->foreignUuid('role_id')->constrained()->onDelete('cascade');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
