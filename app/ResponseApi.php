<?php

namespace App;

trait ResponseApi
{
    protected function successResponse($data = null, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = null, $code = 400, $errors = null)
    {
        $data = [
            'success' => false,
            'message' => $message,
            'data' => null
        ];

        if ($errors !== null && !empty($errors)) {
            $data['errors'] = $errors;
        }

        return response()->json($data, $code);
    }
}
