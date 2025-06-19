<?php

use Illuminate\Http\Request;
use App\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    api: __DIR__ . '/../routes/api.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->append(Authorize::class);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->report(function (Throwable $e) {
      if (config('app.debug')) {
        Log::error($e->getMessage(), [
          'exception' => get_class($e),
          'code' => $e->getCode(),
          'trace' => $e->getTraceAsString(),
        ]);
      } else {
        Log::warning($e->getMessage());
      }
    });

    $exceptions->render(function (AuthorizationException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'code' => 403,
          'message' => 'Permission denied'
        ], 403);
      }
    });

    $exceptions->render(function (ModelNotFoundException $e, Request $request) {
      if ($request->is('api/*')) {
        $model = class_basename($e->getModel()); 
  
        return response()->json([
          'code' => 404,
          'message' => "$model not found",
        ], 404);
      }
    });

    $exceptions->render(function (ValidationException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'code' => 400,
          'message' => 'Validation errors',
          'errors' => $e->errors(),
        ], 400);
      }
    });

    $exceptions->render(function (HttpResponseException $e, Request $request) {
  return $e->getResponse(); 
});

    $exceptions->render(function (HttpException $e) {
      return response()->json([
        'code' => $e->getStatusCode(),
        'message' => $e->getMessage(),
      ], $e->getStatusCode());
    });

    $exceptions->render(function (Throwable $e, Request $request) {
      if ($request->is('api/*')) {
        $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $isServerError = $statusCode >= 500;

        return response()->json([
          'code' => $statusCode,
          'message' => config('app.debug') && $isServerError
            ? $e->getMessage()
            : ($isServerError ? 'Internal server error' : $e->getMessage()),
          'trace' => config('app.debug') && $isServerError ? $e->getTrace() : null,
        ], $statusCode);
      }
    });
  })->create();
