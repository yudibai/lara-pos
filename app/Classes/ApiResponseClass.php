<?php

namespace App\Classes;

class ApiResponseClass
{
    /**
     * Create a new class instance.
     */

    // public static function rollback($e, $message = "Something went wrong! Process not completed"){
    //     DB::rollBack();
    //     self::throw($e, $message);
    // }

    // public static function throw($e, $message = "Something went wrong! Process not completed"){
    //     Log::info($e);
    //     throw new HttpResponseException(response()->json(["message"=> $message], 500));
    // }

    public static function sendResponse($message, $code) {
        if ($code != 200 || $code != 201) {
            $response = [
                'success'       =>  false,
                'message'       =>  $message,
                'status_code'   =>  $code,
            ];
        } else {
            $response = [
                'success'       =>  true,
                'message'       =>  $message,
                'status_code'   =>  $code,
            ];
        }
        return response()->json($response, $code);
    }
}
