<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                try {
                    \Illuminate\Support\Facades\Log::error('API Exception: ' . $e->getMessage(), [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                    ]);

                    $response = [
                        'success' => false,
                        'message' => $e->getMessage() ?: 'An error occurred',
                    ];

                    if (config('app.debug', false)) {
                        $response['debug'] = [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => collect($e->getTrace())
                                ->take(10)
                                ->map(fn($trace) => \Illuminate\Support\Arr::except($trace, ['args']))
                                ->all()
                        ];
                    }

                    $statusCode = match (true) {
                        $e instanceof ValidationException => 422,
                        $e instanceof AuthenticationException => 401,
                        $e instanceof AuthorizationException => 403,
                        $e instanceof ModelNotFoundException => 404,
                        $e instanceof NotFoundHttpException => 404,
                        $e instanceof HttpException => $e->getStatusCode(),
                        $e instanceof QueryException => 500,
                        default => 500
                    };

                    if ($e instanceof ValidationException) {
                        $response['errors'] = $e->errors();
                    }

                    if ($e instanceof HttpException && empty($response['message'])) {
                        $response['message'] = match($statusCode) {
                            400 => 'Bad Request',
                            401 => 'Unauthorized',
                            403 => 'Forbidden',
                            404 => 'Not Found',
                            422 => 'Validation Error',
                            500 => 'Internal Server Error',
                            default => 'An error occurred'
                        };
                    }

                    return response()->json($response, $statusCode);

                } catch (\Throwable $renderException) {
                    \Illuminate\Support\Facades\Log::critical('Exception handler failed', [
                        'original_exception' => [
                            'class' => get_class($e),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ],
                        'render_exception' => [
                            'class' => get_class($renderException),
                            'message' => $renderException->getMessage(),
                            'file' => $renderException->getFile(),
                            'line' => $renderException->getLine(),
                        ]
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Internal Server Error',
                        'error_code' => 'EXCEPTION_HANDLER_FAILED'
                    ], 500);
                }
            }

            return null;
        });

        $exceptions->report(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    })->create();