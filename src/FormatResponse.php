<?php
namespace Wewillapp;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Exception;
use Log;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class FormatResponse
{
    /**
     * Format Success Response
     *
     * @param   Array  $data        Response data
     * @param   String  $message     Response Detail
     * @param   Integer  $statusCode  Response Status
     *
     * @return  Json               Response Data (Formatted)
     */
    public static function success($data = null, $message = null, $statusCode = 200)
    {
        if (!is_null($data)) {
            $response['data'] = $data;
        }
        if (!is_null($message)) {
            $response['message'] = $message;
        }
        return response()->json($response, $statusCode);
    }
    /**
     * Format Error Response
     *
     * @param   String  $message     Error Message
     * @param   Integer  $statusCode  Response Status
     *
     * @return  Json               Response Data (Formatted)
     */
    public static function error($message, $statusCode = 400)
    {
        return response()->json([
            'errors' => [
                'message' => $message,
                'statusCode' => $statusCode
            ],
        ], $statusCode);
    }
    /**
     * Handler Error Response
     *
     * @param   Mixed  $exception  Exception Class
     *
     * @return  json              Formatted Error Response
     */
    public static function render($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        }
        if (method_exists($exception, 'getCode')) {
            $statusCode = $exception->getCode() == 0 ? 500 : $exception->getCode();
        }
        switch ($statusCode) {
            case 404:
                $message = "NOT_FOUND";
                break;
            case 401:
                $message = "UNAUTHORIZED";
                break;
            case 403:
                $message = "FORBIDDEN";
                break;
            case 405:
                $message = "METHOD_NOT_ALLOWED";
                break;
            default:
                $statusCode = 500;
                $message = "SOMETHING_WENT_WRONG";
                break;
        }
        if (method_exists($exception, 'getMessage')) {
            $message = strtoupper(snake_case($exception->getMessage()));
        }

        if ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
        } if ($exception instanceof ValidationException) {
            $statusCode = isset($exception->status) ? $exception->status : 422;
            $message = snake_case($exception->validator->errors()->first());
        } if ($exception instanceof AuthenticationException) {
            $statusCode = 401;
        } if ($exception instanceof MethodNotAllowedException) {
            $statusCode = 401;
        }

        if (env('APP_DEBUG') == true) {
            $errorFile = $exception->getFile();
            $errorLine = $exception->getLine();
            Log::error("API ERROR: ".$message." FILE: ".$errorFile. " LINE: ".$errorLine);

            return response()->json([
                'errors' => [
                    'message' => $message,
                    'statusCode' => $statusCode,
                    'detail' => [
                        'file' => $errorFile,
                        'line' => $errorLine,
                        'rawMessage' => $exception->getMessage()
                    ]
                ]
            ], $statusCode);
        }
        return self::error($message, $statusCode);
    }
}
