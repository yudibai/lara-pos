<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route; // untuk get route => $routeName = Route::currentRouteName();
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\LogController;
use App\Classes\ApiResponseClass;

class CustomerController extends Controller
{
    public function store() {
        if (request()->getMethod() == 'POST')
        {
            $messages = array(
                'name.required' => 'Nama pelanggan harus di isi',
                'name.max' => 'Nama pelanggan tidak boleh lebih dari 40 karakter',
                'phone.max' => 'Nomor telephone tidak boleh lebih dari 15 karakter',
            );

            $validator = Validator::make(request()->all(), [
                'name'                  => 'required|string|max:40',
                'phone'                 => 'string|max:15',
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'    =>  false,
                    'error'     =>  'VALIDATION_ERROR',
                    'message'   =>  $validator->errors()->first(),
                ], 422);
            }

            if (request()->id > 0) {
                // update table
                $findCustomer = DB::table('customer')->where('id', request()->id)->first();
                
                if ($findCustomer != null) {
                    $log = new LogController();

                    DB::table('customer')
                        ->where('id', request()->id)
                        ->update([
                            'name'                  => request()->name,
                            'gender'                => request()->gender,
                            'phone'                 => request()->phone,
                            'email'                 => request()->email,
                            'address'               => request()->address,
                            'note'                  => request()->note,
                            'updated_at'            => Carbon::now()->toDateTimeString(),
                        ]);

                    $log->store("customer", 2, request()->id, auth()->user()->id, Carbon::now()->toDateTimeString());
                } else {
                    return response()->json([
                        'status'    =>  false,
                        'error'     =>  'ERROR_NOT_FOUND',
                        'message'   =>  'Data pelanggan tidak ditemukan',
                    ], 422);
                }
            } else {
                $routeName = Route::currentRouteName();
                if ($routeName == "create-customer") {
                    // insert to table
                    DB::table('customer')->insert([
                        'name'                  => request()->name,
                        'gender'                => request()->gender, // 0 is women || 1 is man
                        'phone'                 => request()->phone,
                        'email'                 => request()->email,
                        'address'               => request()->address,
                        'note'                  => request()->note,
                        'created_at'            => Carbon::now()->toDateTimeString(),
                    ]);
                    $lastId = DB::getPdo()->lastInsertId();
    
                    $log = new LogController();
                    $log->store("customer", 1, $lastId, auth()->user()->id, Carbon::now()->toDateTimeString());
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
            $getCustomers = DB::table('customer')->get();
            return response()->json([
                'status'    =>  true,
                'message'   =>  $getCustomers->count() > 0 ? "Berhasil mendapatkan semua data pelanggan" : "Data pelanggan kosong",
                'data'      =>  $getCustomers
            ], 201);
        }
    }

    public function delete() {
        $id = request()->id;

        $findCustomer = DB::table('customer')->where('id', $id)->first();
        if ($findCustomer != null) {
            $hasDelete = DB::table('customer')->where('id', $id)->delete();
            
            if ($hasDelete == 1) {
                $log = new LogController();
                $log->store("customer", 3, $id, auth()->user()->id, Carbon::now()->toDateTimeString());
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
