<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResponseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
  Route::post('/signup', 'signup');
  Route::post('/verify-email/{verificationToken}', 'verifyEmail');
  Route::post('/resend-verirification', 'resendVerification');
  Route::post('/signin', 'signin');
  Route::post('/refresh-token', 'refreshToken');
  Route::post('/reset-password/{resetToken}', 'resetPassword');
  Route::post('/request-reset-password', 'requestResetPassword');
  Route::post('/signout', 'signout')->middleware('auth:api');
});

Route::prefix('dashboard')->middleware('auth:api')->controller(DashboardController::class)->group(function () {
  Route::middleware('authorize:admin')->group(function () {
    Route::get('/', 'stats');
  });
});

Route::prefix('users')->middleware('auth:api')->controller(UserController::class)->group(function () {
  Route::middleware('authorize:admin')->group(function () {
    Route::get('/search', 'search');
    Route::post('/', 'create');
    Route::patch('/{user}', 'update');
    Route::delete('/{user}', 'delete');
  });

  Route::middleware('authorize:user,admin')->group(function () {
    Route::get('/{user}', 'show');
    Route::patch('/{user}/profile', 'profile');
  });
});

Route::prefix('roles')->middleware('auth:api')->controller(RoleController::class)->group(function () {
  Route::middleware('authorize:admin')->group(function () {
    Route::get('/search', 'search');
    Route::get('/', 'list');
    Route::post('/', 'create');
    Route::get('/{role}', 'show');
    Route::patch('/{role}', 'update');
    Route::delete('/{role}', 'delete');
  });
});

Route::prefix('categories')->middleware('auth:api')->controller(CategoryController::class)->group(function () {
  Route::middleware('authorize:user,admin')->group(function () {
    Route::get('/', 'list');
  });

  Route::middleware('authorize:admin')->group(function () {
    Route::get('/search', 'search');
    Route::post('/', 'create');
    Route::get('/{category}', 'show');
    Route::patch('/{category}', 'update');
    Route::delete('/{category}', 'delete');
  });
});

Route::prefix('complaints')->middleware('auth:api')->group(function () {
  Route::controller(ComplaintController::class)->group(function () {
    Route::middleware('authorize:user')->group(function () {
      Route::post('/', 'create');
      Route::patch('/{complaint}', 'update');
    });

    Route::middleware('authorize:user,admin')->group(function () {
      Route::get('/search', 'search');
      Route::get('/{complaint}', 'show');
      Route::delete('/{complaint}', 'delete');
      Route::post('/{complaint}/images', 'uploadImage');
      Route::delete('/{complaint}/images', 'deleteImage');
    });
  });

  Route::controller(ResponseController::class)->group(function () {
    Route::middleware('authorize:admin')->group(function () {
      Route::post('/{complaint}/responses', 'create');
      Route::patch('/{complaint}/responses/{response}', 'update');
      Route::delete('/{complaint}/responses/{response}', 'delete');
    });

    Route::middleware('authorize:user,admin')->group(function () {
      Route::get('/{complaint}/responses', 'list');
      Route::get('/{complaint}/responses/{response}', 'show');
    });
  });
});


