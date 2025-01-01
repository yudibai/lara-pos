<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route; // untuk get route => $routeName = Route::currentRouteName();
use Validator;
use Carbon\Carbon;

class ProductCategoryController extends Controller
{
    public function store() {
        $log = new LogController();
        if (request()->getMethod() == 'POST') {
            $messages = array(
                'name.required' => 'Nama produk kategori harus di isi',
                'name.max' => 'Nama produk kategori tidak boleh lebih dari 20 karakter',
            );

            $validator = Validator::make(request()->all(), [
                'name'                  => 'required|string|max:20',
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
                $findProduct = DB::table('product_category')->where('id', request()->id)->first();
                
                if ($findProduct != null) {
                    DB::table('product_category')
                        ->where('id', request()->id)
                        ->update([
                            'name'                  => request()->name,
                            'updated_at'            => Carbon::now()->toDateTimeString(),
                        ]);

                    $log->store("product_category", 2, request()->id, auth()->user()->id, Carbon::now()->toDateTimeString());
                } else {
                    return response()->json([
                        'status'    =>  false,
                        'error'     =>  'ERROR_NOT_FOUND',
                        'message'   =>  'Produk kategori tidak ditemukan',
                    ], 422);
                }
            } else {
                $routeName = Route::currentRouteName();
                if ($routeName == "create-product-category") {
                    // insert to table
                    DB::table('product_category')->insert([
                        'owner_id'              => auth()->user()->id,
                        'name'                  => request()->name,
                        'created_at'            => Carbon::now()->toDateTimeString(),
                    ]);
                    $lastId = DB::getPdo()->lastInsertId();

                    $log->store("product_category", 1, $lastId, auth()->user()->id, Carbon::now()->toDateTimeString());
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
                'message'   =>  request()->id > 0 ? "Produk kategori berhasil diubah" : "Produk kategori berhasil disimpan"
            ], 201);
        } else {
            $getPlace = DB::table('place')->where("id", auth()->user()->place_id)->first();
            $getProductCategories = DB::table('product_category')->where("owner_id", $getPlace->owner_id)->orderBy('id', 'desc')->get();
            return response()->json([
                'status'    =>  true,
                'message'   =>  $getProductCategories->count() > 0 ? "Berhasil mendapatkan semua produk kategori" : "Data produk kategori kosong",
                'data'      =>  $getProductCategories
            ], 201);
        }
    }

    public function delete() {
        $id = request()->id;

        $findProduct = DB::table('product_category')->where('id', $id)->first();
        if ($findProduct != null) {
            // ini untuk mengecek apakah product category tersebut digunakan atau tidak
            $checkUseProCat = DB::table('products')->where("product_category_id", $id)->get();
            if ($checkUseProCat->count() > 0) {
                // return ApiResponseClass::sendResponse("Produk kategori ini tidak dapat di hapus karna sedang digunakan pada salah satu produk", 100);
                return response()->json([
                    'status'    =>  true,
                    'message'   =>  "Produk kategori ini tidak dapat di hapus karna sedang digunakan pada salah satu produk"
                ], 100);
            } else {
                $hasDelete = DB::table('product_category')->where('id', $id)->delete();
                if ($hasDelete == 1) {
                    $log->store("product_category", 1, $id, auth()->user()->id, Carbon::now()->toDateTimeString());
                }
            }
            return response()->json([
                'status'    =>  true,
                'message'   =>  "Produk kategori berhasil dihapus"
            ], 201);
        } else {
            return response()->json([
                'status'    =>  false,
                'error'     =>  'ERROR_NOT_FOUND',
                'message'   =>  'Produk kategori tidak ditemukan',
            ], 422);
        }
    }
}
