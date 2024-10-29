<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 401);
            }
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => "Record not found",
                ], 404);
                // return ResponseError($exception, "Record not found", 404);
            }
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => "This action is unauthorized",
                ], 403);
                // return ResponseError($exception,"This action is unauthorized",403);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => "This action is unauthorized",
                ], 403);
                // return ResponseError($exception,"This action is unauthorized",403);
            }
        });

        $exceptions->render(function (QueryException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => "An error occurred while retrieving data. Please try again later",
                ], 500);
                // return ResponseError($exception,"An error occurred while retrieving data. Please try again later.",500);
            }

        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => "You have to login first",
                ], 401);
                // return ResponseError($exception,"You have to login first",401);
            }
        });
    })->create();
