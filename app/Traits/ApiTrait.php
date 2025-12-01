<?php 

namespace App\Traits;

trait ApiTrait
{
    public function respondWithSuccess($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 200,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function respondWithError($message = 'Error', $code = 400, $errors = [])
    {
        return response()->json([
            'status' => 500,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}