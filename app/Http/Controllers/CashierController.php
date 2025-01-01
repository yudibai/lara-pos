<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\LogController;
use Illuminate\Support\Str;
use App\Classes\ApiResponseClass;

class CashierController extends Controller
{
    public function store() {
        $getPlace = DB::table('place')->where("id", auth()->user()->place_id)->first();
        $getProducts = DB::table('products')
            ->where("owner_id", $getPlace->owner_id)
            ->orderBy('id', 'desc')->get();
        
        $putDataCashierByPlace = [];
        foreach ($getProducts as $product) {
            $dataActiveToArr = explode(',', $product->active_by_placeid);
            if (in_array(auth()->user()->place_id, $dataActiveToArr)) {
                $putDataCashierByPlace[] = $product;
            }
        }

        return response()->json([
            'status'    =>  true,
            'message'   =>  count($putDataCashierByPlace) > 0 ? "Berhasil mendapatkan data produk kasir" : "Data produk kosong",
            'data'      =>  $putDataCashierByPlace
        ], 201);
    }
}
