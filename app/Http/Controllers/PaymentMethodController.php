<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route; // untuk get route => $routeName = Route::currentRouteName();
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\LogController;

class PaymentMethodController extends Controller
{
    public function store() {
        if (request()->getMethod() == 'POST')
        {
            $messages = array(
                'user_id.required' => 'User ID harus di isi',
            );

            $validator = Validator::make(request()->all(), [
                'user_id'               => 'required',
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'    =>  false,
                    'error'     =>  'VALIDATION_ERROR',
                    'message'   =>  $validator->errors()->first(),
                ], 422);
            }


            $methods = [
                {
                    "name": "Cash",
                    "own": true,
                    "datas": [],
                    "image": ""
                },
                {
                    "name": "hasQRIS",
                    "own": false,
                    "datas": [],
                    "image": ""
                },
                {
                    "name": "transfer",
                    "own": false,
                    "datas": []
                    "image": ""
                },
            ];

            if (request()->id > 0) {
                // update table
                $findCustomer = DB::table('payment_method')->where('id', request()->id)->first();
                
                if ($findCustomer != null) {
                    $log = new LogController();

                    DB::table('payment_method')
                        ->where('id', request()->id)
                        ->update([
                            'user_id'               => request()->user_id,
                            'methods'               => json_decode($methods),
                            'updated_at'            => Carbon::now()->toDateTimeString(),
                        ]);

                    $log->store("customer", 2, request()->id, request()->user_id, Carbon::now()->toDateTimeString());
                } else {
                    return response()->json([
                        'status'    =>  false,
                        'error'     =>  'ERROR_NOT_FOUND',
                        'message'   =>  'Data pembayaran tidak ditemukan',
                    ], 422);
                }
            } else {
                $routeName = Route::currentRouteName();
                if ($routeName == "create-customer") {
                    // insert to table
                    DB::table('payment_method')->insert([
                        'user_id'               => request()->user_id,
                        'methods'               => json_decode($methods),
                        'created_at'            => Carbon::now()->toDateTimeString(),
                    ]);
                    $lastId = DB::getPdo()->lastInsertId();
    
                    $log = new LogController();
                    $log->store("customer", 1, $lastId, request()->user_id, Carbon::now()->toDateTimeString());
                } else {
                    return response()->json([
                        'status'    =>  false,
                        'error'     =>  'ERROR',
                        'message'   =>  'Gagal melakukan action',
                    ], 422);
                }
            }
    
            return response()->json([
                'status'    =>  true,
                'message'   =>  request()->id > 0 ? "Data pelanggan berhasil diubah" : "Data pelanggan berhasil disimpan"
            ], 201);
        } else {
            $getCustomers = DB::table('payment_method')->get();
            return response()->json([
                'status'    =>  true,
                'message'   =>  $getCustomers->count() > 0 ? "Berhasil mendapatkan semua data pelanggan" : "Data pelanggan kosong",
                'data'      =>  $getCustomers
            ], 201);
        }
    }

    public function delete() {
        $id = request()->id;

        $findCustomer = DB::table('payment_method')->where('id', $id)->first();
        if ($findCustomer != null) {
            $hasDelete = DB::table('payment_method')->where('id', $id)->delete();
            
            if ($hasDelete == 1) {
                $log = new LogController();
                $log->store("customer", 3, $id, request()->user_id, Carbon::now()->toDateTimeString());
            }
            return response()->json([
                'status'    =>  true,
                'message'   =>  "Data pelanggan berhasil dihapus"
            ], 201);
        } else {
            return response()->json([
                'status'    =>  false,
                'error'     =>  'ERROR_NOT_FOUND',
                'message'   =>  'Data pelanggan tidak ditemukan',
            ], 422);
        }

    }
}
