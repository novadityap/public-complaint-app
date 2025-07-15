<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Mail\VerifyEmail;
use App\Mail\ResetPassword;

describe('POST /api/auth/signup', function () {
  beforeEach(function () {
    Mail::fake();
  });

  afterEach(function () {
    Mail::clearResolvedInstances();
    removeAllTestUsers();
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->postJson('/api/auth/signup', [
      'username' => '',
      'email' => '',
      'password' => '',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.username'))->toBeArray();
    expect($result->json('errors.email'))->toBeArray();
    expect($result->json('errors.password'))->toBeArray();
  });

  it('should return an error if email already in use', function () {
    createTestUser();

    $result = $this->postJson('/api/auth/signup', [
      'username' => 'test',
      'email' => 'test@me.com',
      'password' => 'test123',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.email'))->toBeArray();
    Mail::assertNothingSent();
  });

  it('should create a new user and send verification email', function () {
    $result = $this->postJson('/api/auth/signup', [
      'username' => 'test',
      'email' => 'test@me.com',
      'password' => 'test123',
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Please check your email to verify your account');

    Mail::assertSent(VerifyEmail::class, function ($mail) {
      return $mail->hasTo('test@me.com');
    });

    $user = getTestUser();

    expect($user)->not->toBeNull();
    expect($user->is_verified)->toBeTrue();
  });
});

describe('POST /api/auth/verify-email/{token}', function () {
  beforeEach(function () {
    createTestUser(['verification_token' => '123']);
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if verification token has expired', function () {
    $updatedUser = updateTestUser([
      'verification_token_expires' => now()->subMinutes(5),
    ]);
    $result = $this->postJson("/api/auth/verify-email/{$updatedUser->verification_token}");

    expect($result->status())->toBe(401);
    expect($result->json('message'))->toBe('Verification token is invalid or has expired');
  });

  it('should verify email if verification token is valid', function () {
    $updatedUser = updateTestUser([
      'verification_token' => '123',
      'verification_token_expires' => now()->addMinutes(5),
    ]);

    $result = $this->postJson("/api/auth/verify-email/{$updatedUser->verification_token}");

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Email verified successfully');
  });
});

describe('POST /api/auth/resend-verification', function () {
  beforeEach(function () {
    Mail::fake();
  });

  afterEach(function () {
    Mail::clearResolvedInstances();
    removeAllTestUsers();
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->postJson('/api/auth/resend-verification', [
      'email' => '',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('errors.email'))->toBeArray();
  });

  it('should not send verification email if user is not registered', function () {
    $result = $this->postJson('/api/auth/resend-verification', [
      'email' => 'test@me.com',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    Mail::assertNothingSent();
  });

  it('should send verification email if user is registered', function () {
    createTestUser();

    $result = $this->postJson('/api/auth/resend-verification', [
      'email' => 'test@me.com',
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Please check your email to verify your account');
    Mail::assertSent(VerifyEmail::class, 1);
  });
});

describe('POST /api/auth/signin', function () {
  beforeEach(function () {
    createTestUser();
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->postJson('/api/auth/signin', [
      'email' => '',
      'password' => '',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('errors.email'))->toBeArray();
    expect($result->json('errors.password'))->toBeArray();
  });

  it('should return an error if credentials are invalid', function () {
    $result = $this->postJson('/api/auth/signin', [
      'email' => 'test@me.co',
      'password' => 'test12',
    ]);

    expect($result->status())->toBe(401);
    expect($result->json('message'))->toBe('Email or password is invalid');
  });

  it('should sign in if credentials are valid', function () {
    updateTestUser(['is_verified' => true]);

    $result = $this->postJson('/api/auth/signin', [
      'email' => 'test@me.com',
      'password' => 'test123',
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('data.token'))->toBeString();
    expect($result->json('data.username'))->toBe('test');
    expect($result->json('data.email'))->toBe('test@me.com');
    expect($result->json('data.role'))->not->toBeNull();

    $decoded = JWT::decode(
      $result->json('data.token'),
      new Key(config('auth.jwt_secret'), config('auth.jwt_algo'))
    );

    expect($decoded->sub)->not->toBeNull();
    expect($decoded->role)->not->toBeNull();
    expect($result->headers->get('set-cookie'))->toContain('refreshToken=');
  });
});

describe('POST /api/auth/signout', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestRefreshTokens();
  });

  it('should return an error if refresh token is not provided', function () {
    $result = $this->postJson('/api/auth/signout', [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(401);
    expect($result->json('message'))->toBe('Refresh token is not provided');
  });

  it('should return an error if refresh token is not found in the database', function () {
    $result = $this->withCookie('refreshToken', test()->validUUID)
      ->post('/api/auth/signout', [], [
        'Authorization' => 'Bearer ' . test()->accessToken,
      ]);

    expect($result->status())->toBe(401);
    expect($result->json('message'))->toBe('Refresh token is invalid');
  });

  it('should sign out if refresh token is valid', function () {
    $refreshToken = createTestRefreshToken();
    $result = $this->withUnencryptedCookie('refreshToken', $refreshToken->token)->post('/api/auth/signout', [], [
      'Authorization' => 'Bearer ' . test()->accessToken
    ]);

    expect($result->status())->toBe(204);
  });
});

describe('POST /api/auth/refresh-token', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestRefreshTokens();
  });

  it('should return an error if refresh token is not provided', function () {
    $result = $this->post('/api/auth/refresh-token', [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(401);
    expect($result->json('message'))->toBe('Refresh token is not provided');
  });

  it('should return an error if refresh token is not found in the database', function () {
    $result = $this
      ->withCookie('refreshToken', test()->validUUID)
      ->post('/api/auth/refresh-token', [], [
        'Authorization' => 'Bearer ' . test()->accessToken,
      ]);

    expect($result->status())->toBe(401);
    expect($result->json('message'))->toBe('Refresh token is invalid');
  });

  it('should refresh token if refresh token is valid', function () {
    createTestRefreshToken();
    $refreshToken = getTestRefreshToken();

    $result = $this
      ->withUnencryptedCookie('refreshToken', $refreshToken->token)
      ->post('/api/auth/refresh-token', [], [
        'Authorization' => 'Bearer ' . test()->accessToken,
      ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Token refreshed successfully');

    $decoded = JWT::decode(
      $result->json('data.token'),
      new Key(config('auth.jwt_secret'), config('auth.jwt_algo'))
    );

    expect($decoded->sub)->not->toBeEmpty();
    expect($decoded->role)->not->toBeEmpty();
  });
});

describe('POST /api/auth/request-reset-password', function () {
  beforeEach(function () {
    Mail::fake();
  });

  afterEach(function () {
    removeAllTestUsers();
    Mail::clearResolvedInstances();
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->postJson('/api/auth/request-reset-password', [
      'email' => '',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('errors.email'))->toBeArray();
  });

  it('should not send reset password email if user is not registered', function () {
    $result = $this->postJson('/api/auth/request-reset-password', [
      'email' => 'test1@me.com',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
  });

  it('should send reset password email if user is registered', function () {
    createTestUser(['is_verified' => true]);

    $result = $this->postJson('/api/auth/request-reset-password', [
      'email' => 'test@me.com',
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Please check your email to reset your password');
    Mail::assertSent(ResetPassword::class, 1);
  });
});

describe('POST /api/auth/reset-password/{token}', function () {
  beforeEach(function () {
    createTestUser();
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->postJson('/api/auth/reset-password/invalid-token', [
      'newPassword' => '',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('errors.newPassword'))->toBeArray();
  });

  it('should return an error if reset token has expired', function () {
    updateTestUser([
      'reset_token' => '123',
      'reset_token_expires' => now()->subMinutes(5),
    ]);

    $result = $this->postJson('/api/auth/reset-password/123', [
      'newPassword' => 'test123',
    ]);

    expect($result->status())->toBe(401);
    expect($result->json('message'))->toBe('Reset token is invalid or has expired');
  });

  it('should reset password if reset token is valid', function () {
    updateTestUser([
      'reset_token' => '123',
      'reset_token_expires' => now()->addMinutes(5),
    ]);

    $result = $this->postJson('/api/auth/reset-password/123', [
      'newPassword' => 'test123',
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Password reset successfully');
  });
});

