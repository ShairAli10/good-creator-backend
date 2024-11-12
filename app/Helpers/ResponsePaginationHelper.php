<?php

namespace App\Helpers;

class ResponsePaginationHelper
{
    public static function jsonPaginationResponse($status, $message, $totalPages, $data)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'totalpages' => $totalPages,
            'data' => $data,
        ];
        return response()->json($response, 200);
    }
}
