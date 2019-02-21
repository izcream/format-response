<?php
namespace Wewillapp;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class FormatResponse
{
    private static function error($message, $statusCode = 400)
    {
        return response()->json([
            'errors' => [
                'message' => $message,
            ],
        ], $statusCode);
    }

    public static function render($exception)
    {
        $statusCode = 500;
        $message = snake_case($exception->getMessage());
        if ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
        } elseif ($exception instanceof ValidationException) {
            $statusCode = isset($exception->status) ? $exception->status : 422;
            $message = snake_case($exception->validator->errors()->first());
        } elseif ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $statusCode = 401;
        } else {
            $statusCode = isset($exception->status) ? $exception->status : 500;
        }
        return self::error($message, $statusCode);
    }
}
