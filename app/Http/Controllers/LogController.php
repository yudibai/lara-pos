<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route; // untuk get route => $routeName = Route::currentRouteName();
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class LogController extends Controller
{
    public function store(String $table = "", int $status = 0, int $id = 0, int $userId = 0, String $timeCreated = "") {
        if (request()->getMethod() == 'GET')
        {
            $getLogs = DB::table('logs')->get();
            if ($getLogs->count() > 0) {
                return response()->json([
                    'status'    =>  true,
                    'message'   =>  "Data ditemukan",
                    'data'      =>  $getLogs
                ], 201);
            } else {
                return response()->json([
                    'status'    =>  true,
                    'message'   =>  "Data log kosong"
                ], 201);
            }
        } else {
            // INFO ....
            // Status == 1  ->  Created
            // Status == 2  ->  Updated
            // Status == 3  ->  Deleted

            $postLog = DB::table('logs')->insert([
                'detail'        =>  json_encode([
                        'table'         =>  $table,
                        'status'        =>  $status,
                        'id'            =>  $id,
                        'userid'        =>  $userId,
                        'timeCreated'   =>  $timeCreated,
                    ]),
                'created_at'    => Carbon::now()->toDateTimeString(),
            ]);

            return response()->json([
                'status'    =>  true,
                'message'   =>  "Data berhasil disimpan",
            ], 201);
        }
    }
}
