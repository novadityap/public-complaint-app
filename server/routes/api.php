<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
  Route::post('/signup', 'signup');
  Route::post('/verify-email', 'verifyEmail');
  Route::post('/resend-verirification', 'resendVerification');
  Route::post('/signin', 'signin');
  Route::post('/refresh-token', 'refreshToken');
  Route::post('/reset-password', 'resetPassword');
  Route::post('/request-reset-password', 'requestResetPassword');
  Route::post('/signout', 'signout')->middleware('auth:api');
});

Route::prefix('users')->middleware('auth:api')->controller(UserController::class)->group(function () {
  Route::middleware('authorize:admin')->group(function () {
    Route::post('/', 'create');
    Route::get('/search', 'search');
    Route::patch('/{user}', 'update');
    Route::delete('/{user}', 'delete');
  });
  Route::middleware('authorize:user,admin')->group(function () {
    Route::get('/{user}', 'show');
    Route::patch('/{user}/profile', 'profile');
  });
});

Route::prefix('complaints')->middleware('auth:api')->controller(ComplaintController::class)->group(function () {
  Route::middleware('authorize:admin')->group(function () {
    Route::post('/{complaint}/responses', 'create');
    Route::patch('/{complaint}/responses', 'update');
    Route::delete('/uri: {complaint}/responses', 'delete');
  });
  Route::middleware('authorize:user,admin')->group(function () {
    Route::post('/', 'create');
    Route::get('/{complaint}', 'show');
    Route::patch('/{complaint}', 'update');
    Route::delete('/{complaint}', 'delete');
    Route::post('/{complaint}/images', 'uploadImage');
    Route::delete('/{complaint}/images', 'deleteImage');
    Route::get('/search', 'search');
  });
});


