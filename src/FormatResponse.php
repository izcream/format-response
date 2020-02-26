<?php
namespace Wewillapp;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Exception;
use Log;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Illuminate\Database\QueryException;

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
        // dd($exception);
        $statusCode = 500;
        $message = $exception->getMessage();
        if (method_exists($exception, 'getCode')) {
            $statusCode = $exception->getCode();
        }
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        }
        switch ($statusCode) {
            case 403:
                $message = 'forbidden';
                break;
            case 405:
                $message = 'method not allowed';
                break;
            case 500:
            case 0:
            case -1:
                $message = 'something went wrong';
                $statusCode = 500;
                break;
            default:
                $message = 'unknow error';
                break;
        }

        if ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $message = 'not found';
        } if ($exception instanceof ValidationException) {
            $statusCode = isset($exception->status) ? $exception->status : 422;
            $message = $exception->validator->errors()->first();
        } if ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $message = 'unauthorized';
        } if ($exception instanceof MethodNotAllowedException) {
            $statusCode = 405;
            $message = 'unauthorized';
        }
        if ($exception instanceof QueryException) {
            $statusCode = 500;
            $message = 'something went wrong';
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
