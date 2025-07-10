<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Mail\VerifyEmail;
use App\Mail\ResetPassword;
use Illuminate\Support\Str;
use App\Models\RefreshToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\Auth\SigninRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Http\Requests\Auth\ResetPasswordActionRequest;

class AuthController extends Controller
{
  private function generateUsername(string $username, int $count): string
  {
    $slug = Str::slug($username);
    return $count > 0 ? "{$slug}{$count}" : $slug;
  }

  public function signup(SignupRequest $request): JsonResponse
  {
    $fields = $request->validated();
    $userRole = Role::where('name', 'user')->first();
    $userPassword = Hash::make($fields['password']);
    $user = User::create([
      'username' => $fields['username'],
      'email' => $fields['email'],
      'password' => $userPassword,
      'role_id' => $userRole->id,
      'is_verified' => true,
      'verification_token' => Str::random(32),
      'verification_token_expires' => Carbon::now()->addHours(24),
    ]);
    $url = config('app.client_url') . "/verify-email/{$user->verification_token}";

    Mail::to($user->email)->send(new VerifyEmail($user, $url));

    Log::info('Verfication email sent successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Please check your email to verify your account'
    ], 200);
  }

  public function verifyEmail(Request $request): JsonResponse
  {
    $user = User::where('verification_token', $request->verificationToken)
      ->where('verification_token_expires', '>', Carbon::now())
      ->first();

    if (!$user)
      abort(401, 'Verification token is invalid or has expired');

    $user->is_verified = true;
    $user->verification_token = null;
    $user->verification_token_expires = null;
    $user->save();

    Log::info('Email verified successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Email verified successfully'
    ], 200);
  }

  public function resendVerification(VerifyEmailRequest $request): JsonResponse
  {
    $user = User::where('email', $request->email)
      ->where('is_verified', false)
      ->first();

    if (!$user)
      throw ValidationException::withMessages([
        'email' => ['Email is not registered']
      ]);

    $user->verification_token = Str::random(32);
    $user->verification_token_expires = Carbon::now()->addHours(24);
    $user->save();

    $url = config('app.client_url') . "/verify-email/{$user->verification_token}";

    Mail::to($user->email)->send(new VerifyEmail($user, $url));

    Log::info('Verfication email sent successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Please check your email to verify your account'
    ], 200);
  }

  public function signin(SigninRequest $request): JsonResponse
  {
    $fields = $request->validated();
    $user = User::with('role')
      ->where('email', $fields['email'])
      ->where('is_verified', true)
      ->first();

    if (!$user || !is_string($user->password) || !Hash::check($fields['password'], $user->password)) {
      abort(401, 'Email or password is invalid');
    }

    $payload = [
      'sub' => $user->id,
      'role' => $user->role->name
    ];

    $token = JWT::encode(
      array_merge($payload, [
        'iat' => now()->timestamp,
        'exp' => now()->addMinutes((int) config('auth.jwt_expires'))->timestamp
      ]),
      config('auth.jwt_secret'),
      config('auth.jwt_algo')
    );

    $refreshToken = JWT::encode(
      array_merge($payload, [
        'iat' => now()->timestamp,
        'exp' => now()->addDays((int) config('auth.jwt_refresh_expires'))->timestamp
      ]),
      config('auth.jwt_refresh_secret'),
      config('auth.jwt_algo')
    );

    $decodedRefreshToken = JWT::decode($refreshToken, new Key(config('auth.jwt_refresh_secret'), config('auth.jwt_algo')));

    RefreshToken::create([
      'token' => $refreshToken,
      'user_id' => $user->id,
      'expires_at' => Carbon::createFromTimestamp($decodedRefreshToken->exp)
    ]);

    $user->token = $token;

    Log::info('Signed in successfully');
    return response()
      ->json([
        'code' => 200,
        'message' => 'Signed in successfully',
        'data' => new UserResource($user),
      ], 200)
      ->cookie(
        'refreshToken',
        $refreshToken,
        config('auth.jwt_refresh_expires'),
        '/',
        null,
        false,
        true,
      );
  }

  public function googleSignin(Request $request): JsonResponse
  {
    if (!$request->code) {
      abort(401, 'Authorization code is not provided');
    }

    $googleUser = Socialite::driver('google')
      ->stateless()
      ->user();

    $user = User::where('email', $googleUser->email)->first();

    if (!$user) {
      $count = 0;
      $username = null;
      $isUsernameTaken = true;

      while ($isUsernameTaken) {
        $username = $this->generateUsername($googleUser->name, $count);
        $existing = User::where('username', $username)->first();
        if (!$existing)
          $isUsernameTaken = false;
        $count++;
      }

      $userRole = Role::where('name', 'user')->first();

      $user = User::create([
        'username' => $username,
        'email' => $googleUser->email,
        'avatar' => config('app.default_avatar_url'),
        'role_id' => $userRole->id,
        'is_verified' => true,
      ]);
    } elseif (!$user->is_verified) {
      $user->is_verified = true;
      $user->save();
    }

    $user->load('role');

    $payload = [
      'sub' => $user->id,
      'role' => $user->role->name
    ];

    $token = JWT::encode(
      array_merge($payload, [
        'iat' => now()->timestamp,
        'exp' => now()->addMinutes((int) config('auth.jwt_expires'))->timestamp
      ]),
      config('auth.jwt_secret'),
      config('auth.jwt_algo')
    );

    $refreshToken = JWT::encode(
      array_merge($payload, [
        'iat' => now()->timestamp,
        'exp' => now()->addDays((int) config('auth.jwt_refresh_expires'))->timestamp
      ]),
      config('auth.jwt_refresh_secret'),
      config('auth.jwt_algo')
    );

    $decodedRefreshToken = JWT::decode($refreshToken, new Key(config('auth.jwt_refresh_secret'), config('auth.jwt_algo')));

    RefreshToken::create([
      'token' => $refreshToken,
      'user_id' => $user->id,
      'expires_at' => Carbon::createFromTimestamp($decodedRefreshToken->exp)
    ]);

    $user->token = $token;

    Log::info('Signed in successfully');
    return response()
      ->json([
        'code' => 200,
        'message' => 'Signed in successfully',
        'data' => new UserResource($user),
      ], 200)
      ->cookie(
        'refreshToken',
        $refreshToken,
        config('auth.jwt_refresh_expires'),
        '/',
        null,
        false,
        true,
      );
  }

  public function signout(Request $request): Response
  {
    $refreshToken = $request->cookie('refreshToken');

    if (!$refreshToken)
      abort(401, 'Refresh token is not provided');

    try {
      JWT::decode($refreshToken, new Key(config('auth.jwt_refresh_secret'), config('auth.jwt_algo')));
    } catch (\Throwable $e) {
      if ($e instanceof ExpiredException) {
        abort(401, 'Refresh token has expired');
      } else {
        abort(401, 'Refresh token is invalid');
      }
    }

    $deletedToken = RefreshToken::where('token', $refreshToken)->delete();
    if (!$deletedToken)
      abort(401, 'Refresh token is invalid');

    Log::info('Signed out successfully');
    return response()->noContent()->withoutCookie('refreshToken');
  }

  public function refreshToken(Request $request): JsonResponse
  {
    $refreshToken = $request->cookie('refreshToken');

    if (!$refreshToken)
      abort(401, 'Refresh token is not provided');

    try {
      JWT::decode($refreshToken, new Key(config('auth.jwt_refresh_secret'), config('auth.jwt_algo')));
    } catch (\Throwable $e) {
      if ($e instanceof ExpiredException) {
        abort(401, 'Refresh token has expired');
      } else {
        abort(401, 'Refresh token is invalid');
      }
    }

    $storedToken = RefreshToken::with('user.role')
      ->where('token', $refreshToken)
      ->where('expires_at', '>', Carbon::now())
      ->first();

    if (!$storedToken)
      abort(401, 'Refresh token is invalid');

    $newToken = JWT::encode(
      [
        'sub' => $storedToken->user->id,
        'role' => $storedToken->user->role->name,
        'email' => $storedToken->user->email,
        'iat' => now()->timestamp,
        'exp' => now()->addMinutes((int) config('auth.jwt_expires'))->timestamp
      ],
      config('auth.jwt_secret'),
      config('auth.jwt_algo')
    );

    return response()->json([
      'code' => 200,
      'message' => 'Token refreshed successfully',
      'data' => ['token' => $newToken]
    ], 200);
  }

  public function requestResetPassword(ResetPasswordRequest $request): JsonResponse
  {
    $fields = $request->validated();

    $user = User::where('email', $fields['email'])
      ->where('is_verified', true)
      ->first();

    if (!$user)
      throw ValidationException::withMessages([
        'email' => ['Email is not registered']
      ]);

    $user->update([
      'reset_token' => Str::random(32),
      'reset_token_expires' => Carbon::now()->addHour()
    ]);

    $url = config('app.client_url') . "/reset-password/{$user->reset_token}";

    Mail::to($user->email)->send(new ResetPassword($user, $url));

    Log::info('Reset password request sent successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Please check your email to reset your password'
    ], 200);
  }

  public function resetPassword(ResetPasswordActionRequest $request): JsonResponse
  {
    $fields = $request->validated();

    $user = User::where('reset_token', $request->resetToken)
      ->where('reset_token_expires', '>', Carbon::now())
      ->first();

    if (!$user)
      abort(401, 'Reset token is invalid or has expired');

    $user->update([
      'password' => Hash::make($fields['new_password']),
      'reset_token' => null,
      'reset_token_expires' => null
    ]);

    Log::info('Password reset successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Password reset successfully'
    ], 200);
  }
}
