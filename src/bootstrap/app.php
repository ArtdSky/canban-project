<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Обработка ошибок аутентификации для API (должно быть первым)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Проверяем, передан ли токен в запросе
                $hasToken = $request->bearerToken() || $request->header('Authorization');

                $message = $hasToken
                    ? 'Токен недействителен или истек.'
                    : 'Нет токена. Требуется заголовок Authorization: Bearer {token}';

                return response()->json([
                    'message' => $message,
                ], 401);
            }
        });

        // Обработка RouteNotFoundException для API (когда пытается редиректить на несуществующий роут)
        $exceptions->render(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Если это ошибка из-за попытки редиректа при аутентификации
                if (str_contains($e->getMessage(), 'login')) {
                    $hasToken = $request->bearerToken() || $request->header('Authorization');

                    $message = $hasToken
                        ? 'Токен недействителен или истек.'
                        : 'Нет токена. Требуется заголовок Authorization: Bearer {token}';

                    return response()->json([
                        'message' => $message,
                    ], 401);
                }
            }
        });

        // Для API роутов возвращаем JSON ответы при других ошибках
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Пропускаем уже обработанные исключения
                if ($e instanceof \Illuminate\Auth\AuthenticationException ||
                    ($e instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException && str_contains($e->getMessage(), 'login'))) {
                    return null;
                }

                $statusCode = 500;
                if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                    $statusCode = $e->getResponse()->getStatusCode();
                } elseif (method_exists($e, 'getStatusCode')) {
                    $statusCode = $e->getStatusCode();
                }

                return response()->json([
                    'message' => $e->getMessage(),
                    'error' => class_basename($e),
                ], $statusCode);
            }
        });
    })->create();
