<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route; // untuk get route => $routeName = Route::currentRouteName();
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\LogController;

class ProductController extends Controller
{
    public function store() {
        if (request()->getMethod() == 'POST')
        {
            $messages = array(
                'product_category_id.required' => 'Produk Category harus di isi',
                'name.required' => 'Nama produk harus di isi',
                'name.max' => 'Nama produk tidak boleh lebih dari 40 karakter',
                'price.required' => 'Harga produk harus di isi',
                'price.numeric' => 'Harga produk hanya boleh angka',
                'price.digits_between' => 'Harga produk tidak boleh lebih dari 9 digit',
                'sku.max' => 'SKU produk tidak boleh lebih dari 15 karakter',
                'plu.max' => 'PLU produk tidak boleh lebih dari 15 karakter',
                'capital.numeric' => 'Harga modal produk hanya boleh angka',
                'capital.digits_between' => 'Harga modal produk tidak boleh lebih dari 9 digit'
            );

            $validator = Validator::make(request()->all(), [
                'product_category_id'   => 'required',
                'name'                  => 'required|string|max:40',
                'price'                 => 'required|numeric|digits_between:1,9',
                'sku'                   => 'max:15',
                'plu'                   => 'max:15',
                'capital'               => 'sometimes|nullable|numeric|digits_between:1,9',
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'    =>  false,
                    'error'     =>  'VALIDATION_ERROR',
                    'message'   =>  $validator->errors()->first(),
                ], 422);
            }

            // Check if userid and product have before
            // $getNameProductByUserId = DB::table('products')
            //     ->where('user_id', auth()->user()->id)
            //     ->where('product_category_id', request()->product_category_id)
            //     ->where('name', request()->name)
            //     ->first();

            // if ($getNameProductByUserId != null) {
            //     return response()->json([
            //         'status'    =>  false,
            //         'message'   =>  "NAME_PRODUCT_AND_CATEGORY_PRODUCT_CANNOT_BE_THE_SAME",
            //     ], 400);
            // }


            // SKU same product
            // $findSameSKU = DB::table('products')
            //     ->where('user_id', auth()->user()->id)
            //     ->where('product_category_id', request()->product_category_id)
            //     ->where('sku', request()->sku)
            //     ->first();

            // if ($findSameSKU != null) {
            //     return response()->json([
            //         'status'    =>  false,
            //         'error'     =>  'VALIDATION_ERROR',
            //         'message'   =>  'SKU sudah terinput',
            //     ], 422);
            // }


            if (request()->id > 0) {
                // update table
                $findProduct = DB::table('products')->where('id', request()->id)->first();
                
                if ($findProduct != null) {
                    $log = new LogController();

                    DB::table('products')
                        ->where('id', request()->id)
                        ->update([
                            'user_id'               => auth()->user()->id,
                            'product_category_id'   => request()->product_category_id,
                            'name'                  => request()->name,
                            'price'                 => request()->price,
                            'image_product'         => request()->image_product,
                            'sku'                   => request()->sku,
                            'plu'                   => request()->plu,
                            'capital'               => request()->capital,
                            'description'           => request()->description,
                            'active'                => request()->active,
                            'updated_at'            => Carbon::now()->toDateTimeString(),
                        ]);

                    $log->store("products", 2, request()->id, auth()->user()->id, Carbon::now()->toDateTimeString());
                } else {
                    return response()->json([
                        'status'    =>  false,
                        'error'     =>  'ERROR_NOT_FOUND',
                        'message'   =>  'Produk tidak ditemukan',
                    ], 422);
                }
            } else {
                $routeName = Route::currentRouteName();
                if ($routeName == "create-product") {
                    // insert to table
                    DB::table('products')->insert([
                        'user_id'               => auth()->user()->id,
                        'product_category_id'   => request()->product_category_id,
                        'name'                  => request()->name,
                        'price'                 => request()->price,
                        'image_product'         => request()->image_product,
                        'sku'                   => request()->sku,
                        'plu'                   => request()->plu,
                        'capital'               => request()->capital,
                        'description'           => request()->description,
                        'active'                => 1,
                        'created_at'            => Carbon::now()->toDateTimeString(),
                    ]);
                    $lastId = DB::getPdo()->lastInsertId();

                    $log = new LogController();
                    $log->store("products", 1, $lastId, auth()->user()->id, Carbon::now()->toDateTimeString());
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
                'message'   =>  request()->id > 0 ? "Produk berhasil diubah" : "Produk berhasil disimpan"
            ], 201);
        } else {
            $getProducts = DB::table('products')->get();
            return response()->json([
                'status'    =>  true,
                'message'   =>  $getProducts->count() > 0 ? "Berhasil mendapatkan semua produk" : "Data produk kosong",
                'data'      =>  $getProducts
            ], 201);
        }
    }

    public function delete() {
        $id = request()->id;

        $findProduct = DB::table('products')->where('id', $id)->first();
        if ($findProduct != null) {
            $hasDelete = DB::table('products')->where('id', $id)->delete();
            
            if ($hasDelete == 1) {
                $log = new LogController();
                $log->store("products", 3, $id, auth()->user()->id, Carbon::now()->toDateTimeString());
            }
            return response()->json([
                'status'    =>  true,
                'message'   =>  "Produk berhasil dihapus"
            ], 201);
        } else {
            return response()->json([
                'status'    =>  false,
                'error'     =>  'ERROR_NOT_FOUND',
                'message'   =>  'Produk tidak ditemukan',
            ], 422);
        }



    }
}
