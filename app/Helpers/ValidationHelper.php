<?php

namespace App\Helpers;

class ValidationHelper
{
    public static function handleValidationErrors($validator)
    {
        if ($validator->fails()) {
            $errors = $validator->errors();
            $response = [
                'status' => false,
                'message' => $errors->first(),
            ];
            return response()->json($response, 200);
        }

        return null; // Validation successful
    }
}