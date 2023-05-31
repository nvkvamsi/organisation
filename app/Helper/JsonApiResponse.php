<?php

namespace App\Helper;

class JsonApiResponse
{

    final static function create(bool $error, int $code, string $message, string $errorDetails, array $data)
    {
        $responseData = [
            'error' => $error,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];
        if (!empty($errorDetails)) {
            $responseData['error_details'] = $errorDetails;
        }
        return $responseData;
    }

    final static function denyPermission()
    {
        $response = JsonApiResponse::create(true, 403, 'Access denied', '', []);
        return response()->json($response);
    }


    final static function success( string $message, array $data = [])
    {
        $response = JsonApiResponse::create(false, 200, $message, '', $data);
        return response()->json($response);
    }

    final static function error(string $message, int $code)
    {
        $response =  JsonApiResponse::create(true, $code, $message, '', []);
        return response()->json($response, $code);
    }
}
