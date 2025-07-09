<?php

use Illuminate\Http\Request;
use App\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    api: __DIR__ . '/../routes/api.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
      'authorize' => Authorize::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->report(function (Throwable $e) {
      $metadata = [
        'exception' => get_class($e),
        'code' => $e->getCode(),
      ];

      if ($e->getCode() < 500) {
        Log::warning($e->getMessage());
      } elseif ($e->getCode() >= 500) {
        if (config('app.debug'))
          $metadata['trace'] = $e->getTraceAsString();
        Log::error($e->getMessage(), $metadata);
      }
    });

    $exceptions->render(function (AuthenticationException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'code' => 401,
          'message' => Auth::guard()->getErrorMessage(),
        ], 401);
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

    $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'code' => 403,
          'message' => 'Permission denied'
        ], 403);
      }
    });

    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
      if ($request->is('api/*')) {
        if ($e->getPrevious() instanceof ModelNotFoundException) {
          $model = class_basename($e->getPrevious()->getModel());

          return response()->json([
            'code' => 404,
            'message' => "{$model} not found",
          ], 404);
        }

        return response()->json([
          'code' => 404,
          'message' => 'Endpoint not found',
        ], 404);
      }
    });

    $exceptions->render(function (ValidationException $e, Request $request) {
      if ($request->is('api/*')) {
        $rawErrors = collect($e->errors());

        $flattened = collect();

        foreach ($rawErrors as $key => $messages) {
          if (Str::contains($key, '.')) {
            $base = Str::camel(Str::before($key, '.'));
            $flattened[$base] = array_merge($flattened[$base] ?? [], $messages);
          } else {
            $flattened[Str::camel($key)] = $messages;
          }
        }

        $flattened = $flattened->map(fn($messages) => array_values(array_unique($messages)));


        return response()->json([
          'code' => 400,
          'message' => 'Validation errors',
          'errors' => $flattened,
        ], 400);
      }
    });

    $exceptions->render(function (HttpException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'code' => $e->getStatusCode(),
          'message' => $e->getMessage(),
        ], $e->getStatusCode());
      }
    });

    $exceptions->render(function (Throwable $e, Request $request) {
      if ($request->is('api/*')) {
        $statusCode = $e instanceof HttpException
          ? $e->getStatusCode()
          : 500;
        $isServerError = $statusCode >= 500;
        $response = [
          'code' => $statusCode,
          'message' => config('app.debug') || !$isServerError
            ? $e->getMessage()
            : 'Internal server error'
        ];

        if (config('app.debug') && $isServerError)
          $response['trace'] = $e->getTrace();
        return response()->json($response, $statusCode);
      }
    });
  })->create();
