<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route; // untuk get route => $routeName = Route::currentRouteName();
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\LogController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Str;

class PlaceController extends Controller
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
                $findPlace = DB::table('place')->where('id', request()->id)->first();
                
                if ($findPlace != null) {
                    $log = new LogController();

                    DB::table('place')
                        ->where('id', request()->id)
                        ->update([
                            'name'                  => request()->name,
                            'phone'                 => request()->phone,
                            'address'               => request()->address,
                            // 'code'                  => Str::random(15),
                            'image'                 => request()->image,
                            'updated_at'            => Carbon::now()->toDateTimeString(),
                        ]);

                    $log->store("place", 2, request()->id, auth()->user()->id, Carbon::now()->toDateTimeString());
                } else {
                    return response()->json([
                        'status'    =>  false,
                        'error'     =>  'ERROR_NOT_FOUND',
                        'message'   =>  'Data tempat tidak ditemukan',
                    ], 422);
                }
            } else {
                $routeName = Route::currentRouteName();
                if ($routeName == "create-place") {
                    $log = new LogController();
                    
                    if (auth()->user()->place_id != null) {
                        return response()->json([
                            'status'    =>  false,
                            'error'     =>  'ERROR',
                            'message'   =>  'Owner sudah menginput tempat',
                        ], 422);
                    }

                    // insert to table place
                    $place = DB::table('place')->insert([
                        'name'                  => request()->name,
                        'phone'                 => request()->phone,
                        'address'               => request()->address,
                        'code'                  => Str::random(15),
                        'image'                 => request()->image,
                        'created_at'            => Carbon::now()->toDateTimeString(),
                    ]);
                    var_dump($place);
                    $lastId = DB::getPdo()->lastInsertId();
                    $log->store("place", 1, $lastId, auth()->user()->id, Carbon::now()->toDateTimeString());

                    DB::table('users')
                        ->where('id', auth()->user()->id)
                        ->update([
                            'place_id'              => $lastId,
                            'updated_at'            => Carbon::now()->toDateTimeString(),
                    ]);
    
                    $log->store("users", 2, auth()->user()->id, auth()->user()->id, Carbon::now()->toDateTimeString());
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
                'message'   =>  request()->id > 0 ? "Data tempat berhasil diubah" : "Data tempat berhasil disimpan"
            ], 201);
        } else {
            $getPlaces = DB::table('place')->get();
            return response()->json([
                'status'    =>  true,
                'message'   =>  $getPlaces->count() > 0 ? "Berhasil mendapatkan semua data tempat" : "Data tempat kosong",
                'data'      =>  $getPlaces
            ], 201);
        }
    }
    
    // public function createPlace(integer $id) {
    //     $hasPlace = DB::table('place')->where('id', $id)->first();
    //     if (is_object($hasPlace)) {

    //     }
    //     DB::table('place')->insert([
    //         'users_id'              => request()->users_id,
    //         'name'                  => request()->name,
    //         'phone'                 => request()->phone,
    //         'address'               => request()->address,
    //         'code'                  => Str::Random(15),
    //         'image'                 => "",
    //         'created_at'            => Carbon::now()->toDateTimeString(),
    //     ]);
    //     $lastId = DB::getPdo()->lastInsertId();

    //     $log = new LogController();
    //     $log->store("place", 1, $lastId, request()->user_id, Carbon::now()->toDateTimeString());
    // }

    public function storeById($id) {
        $idHas = $id;
        if (request()->id != null && request()->id > 0) {
            $idHas = request()->id;
        }
        $getPlaceById = DB::table('place')->where('id', $idHas)->first();
        return response()->json([
            'status'    =>  true,
            'message'   =>  "Berhasil mendapatkan data tempat",
            'data'      =>  $getPlaceById
        ], 201);
    }

    public function delete() {
        $id = request()->id;

        $findPlace = DB::table('place')->where('id', $id)->first();
        if ($findPlace != null) {
            $hasDelete = DB::table('place')->where('id', $id)->delete();
            
            if ($hasDelete == 1) {
                $log = new LogController();
                $log->store("place", 3, $id, request()->user_id, Carbon::now()->toDateTimeString());
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
