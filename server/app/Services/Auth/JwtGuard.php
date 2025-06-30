<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JwtGuard implements Guard
{
  use GuardHelpers;

  protected $message;
  protected $request;
  protected $secret;
  protected $algorithm;

  public function __construct(UserProvider $provider, Request $request) {
    $this->provider = $provider;
    $this->request = $request;
    $this->secret = config('auth.jwt_secret');
    $this->algorithm = config('auth.jwt_algo');
  }

  protected function getTokenFromRequest() {
    $token = $this->request->bearerToken();
    if (!$token) {
      return null;
    }

    return $token;
  }

  public function user() {
    if (!is_null($this->user)) {
      return $this->user;
    }

    $token = $this->getTokenFromRequest();
    if (!$token) {
      $this->message = 'Token is not provided';
      return null;
    }

    try {
      $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
      $user = $this->provider->retrieveById($decoded->sub);

      $this->setUser($user);
      
      return $this->user;
    } catch (\Throwable $e) {
      if ($e instanceof ExpiredException) {
        $this->message = 'Token has expired';
      } else {
        $this->message = 'Token is invalid';
      }

      return null;
    }
  }

  public function validate(array $credentials = []) {
    if (empty($credentials['token'])) {
      return false;
    }

    try {
      $decoded = JWT::decode($credentials['token'], new Key($this->secret, $this->algorithm));
      $user = $this->provider->retrieveById($decoded->sub);
      
      return !is_null($user);
    } catch (\Throwable $e) {
      return false;
    }
  }

  public function getErrorMessage() {
    return $this->message;
  }
}
