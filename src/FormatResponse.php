<?php
namespace Wewillapp;

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class FormatResponse
{

    private $errorMessages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
    ];
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
                'statusCode' => $statusCode,
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
        $statusCode = 500;
        $message = $exception->getMessage();
        if (method_exists($exception, 'getCode')) {
            $statusCode = $exception->getCode();
        }
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        }

        if ($exception instanceof ValidationException) {
            $statusCode = isset($exception->status) ? $exception->status : 422;
            $message = $exception->validator->errors()->first();
        }
        if ($exception instanceof QueryException) {
            $statusCode = 500;
            $message = 'something went wrong';
        }
        $message = $this->errorMessages[$statusCode];

        if (env('APP_DEBUG') == true) {
            $errorFile = $exception->getFile();
            $errorLine = $exception->getLine();

            return response()->json([
                'errors' => [
                    'message' => $message,
                    'statusCode' => $statusCode,
                    'detail' => [
                        'file' => $errorFile,
                        'line' => $errorLine,
                        'rawMessage' => $exception->getMessage(),
                    ],
                ],
            ], $statusCode);
        }
        return self::error($message, $statusCode);
    }

    public function setDefaultErrorMessage($messages)
    {
        foreach ($messages as $key => $value) {
            $this->messages[$key] = $value;
        }
    }
}
