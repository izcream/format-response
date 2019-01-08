<?php
namespace Wewillapp;

class FormatResponse
{
    public static function success($data = [], $message = null)
    {
        if (!is_null($message)) {
            $data['message'] = $message;
        }
        return response()->json([
            'data' => $data,
        ], 200);
    }

    public static function error($message, $statusCode = 400)
    {
        return response()->json([
            'errors' => [
                'message' => $message,
            ],
        ], $statusCode);
    }
}
