<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function jsonResponse($status, $message)
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }
}
