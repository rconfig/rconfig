<?php

namespace App\Traits;

// credit: https://chasingcode.dev/blog/simple-http-response-trait-laravel/

trait RespondsWithHttpStatus
{
    protected function successResponse($message, $data = [], $status = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }

    protected function failureResponse($message, $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    protected function errorResponse($message, $status = 201)
    {
        return $this->failureResponse($message, $status);
    }

    protected function errorResponseWithData($message, $data = [], $status = 201)
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }

    protected function respondNotFound($message, $status = 404)
    {
        return $this->failureResponse($message, $status);
    }
}
