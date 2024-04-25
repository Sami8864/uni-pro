<?php

namespace App\Services;

class ResponseService
{
    public function jsonResponse($code, $message, $data = [])
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }
}