<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\LogController;
use Illuminate\Support\Str;
use App\Classes\ApiResponseClass;

class BankAccountController extends Controller
{
    public function store() {
        if (request()->getMethod() == 'POST')
        {
            $messages = array(
                'owner_id.required'              => "Owner Id harus di isi",
                'bank_name.required'             => "Nama Bank harus di isi",
                'bank_accno.required'            => "Nomor Account harus di isi",
                'bank_username.required'         => "Nama pemilik rekening harus di isi",
            );

            $validator = Validator::make(request()->all(), [
                'bank_name'             => "required",
                'bank_accno'            => "required",
                'bank_username'         => "required",
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
                $findBankAccount = DB::table('bank_account')->where('id', request()->id)->first();
                
                if ($findBankAccount != null) {
                    $log = new LogController();

                    DB::table('bank_account')
                        ->where('id', request()->id)
                        ->update([
                            'bank_name'             => request()->bank_name,
                            'bank_accno'            => request()->bank_accno,
                            'bank_username'         => request()->bank_username,
                            'updated_at'            => Carbon::now()->toDateTimeString(),
                        ]);

                    $log->store("bank_account", 2, request()->id, auth()->user()->id, Carbon::now()->toDateTimeString());
                } else {
                    return response()->json([
                        'status'    =>  false,
                        'error'     =>  'ERROR_NOT_FOUND',
                        'message'   =>  'Data bank tidak ditemukan',
                    ], 422);
                }
            } else {
                $routeName = Route::currentRouteName();
                if ($routeName == "create-bank-account") {
                    // insert to table
                    DB::table('bank_account')->insert([
                        'owner_id'              => request()->owner_id,
                        'bank_name'             => request()->bank_name,
                        'bank_accno'            => request()->bank_accno,
                        'bank_username'         => request()->bank_username,
                        'created_at'            => Carbon::now()->toDateTimeString(),
                    ]);
                    $lastId = DB::getPdo()->lastInsertId();
    
                    $log = new LogController();
                    $log->store("bank_account", 1, $lastId, auth()->user()->id, Carbon::now()->toDateTimeString());
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
                'message'   =>  request()->id > 0 ? "Data bank berhasil diubah" : "Data bank berhasil disimpan"
            ], 201);
        } else {
            $getPlace = DB::table('place')->where("id", auth()->user()->place_id)->first();
            $getBankAccounts = DB::table('bank_account')->where("owner_id", $getPlace->owner_id)->orderBy('id', 'desc')->get();
            return response()->json([
                'status'    =>  true,
                'message'   =>  $getBankAccounts->count() > 0 ? "Berhasil mendapatkan semua data bank" : "Data bank kosong",
                'data'      =>  $getBankAccounts
            ], 201);
        }
    }

    public function delete() {
        $id = request()->id;

        $findBankAcc = DB::table('bank_account')->where('id', $id)->first();
        if ($findBankAcc != null) {
            $hasDelete = DB::table('bank_account')->where('id', $id)->delete();
            
            if ($hasDelete == 1) {
                $log = new LogController();
                $log->store("bank_account", 3, $id, auth()->user()->id, Carbon::now()->toDateTimeString());
            }
            return response()->json([
                'status'    =>  true,
                'message'   =>  "Data bank berhasil dihapus"
            ], 201);
        } else {
            return response()->json([
                'status'    =>  false,
                'error'     =>  'ERROR_NOT_FOUND',
                'message'   =>  'Data bank tidak ditemukan',
            ], 422);
        }
    }
}
