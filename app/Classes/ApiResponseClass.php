<?php

namespace App\Classes;

class ApiResponseClass
{
    /**
     * Create a new class instance.
     */

    public static function rollback($e, $message = "Something went wrong! Process not completed"){
        DB::rollBack();
        self::throw($e, $message);
    }

    public static function throw($e, $message = "Something went wrong! Process not completed"){
        Log::info($e);
        throw new HttpResponseException(response()->json(["message"=> $message], 500));
    }

    // reponse default laravel yaa....
    // public static function sendResponse($message, $code) {
    //     if ($code != 200 || $code != 201) {
    //         $response = [
    //             'success'       =>  false,
    //             'message'       =>  $message,
    //             'status_code'   =>  $code,
    //         ];
    //     } else {
    //         $response = [
    //             'success'       =>  true,
    //             'message'       =>  $message,
    //             'status_code'   =>  $code,
    //         ];
    //     }
    //     return response()->json($response, $code);
    // }

    public static function sendResponse($message, $data, $code) {
        $response = [
            'status'        =>  $code == 200 || $code == 201 ? true : false,
            'message'       =>  $message,
            'data'          =>  $data,
        ];
        return response()->json($response, $code);
    }
    // cara menggunakannya "ApiResponseClass::sendResponse($message, $getProducts, 201);" tidak ada boleh param yang kosong
}
