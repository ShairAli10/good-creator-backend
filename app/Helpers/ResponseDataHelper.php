<?php

namespace App\Helpers;

class ResponseDataHelper
{
    public static function jsonDataResponse($status, $message, $data, $status_code)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
        return response()->json($response, $status_code);
    }
}
