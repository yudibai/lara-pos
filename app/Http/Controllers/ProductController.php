<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route; // untuk get route => $routeName = Route::currentRouteName();
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\LogController;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;
use App\Classes\ApiResponseClass;

class ProductController extends Controller
{
    public function store() {
        if (request()->getMethod() == 'POST')
        {
            $messages = array(
                'name.required'         => 'Nama produk harus di isi',
                'name.max'              => 'Nama produk tidak boleh lebih dari 40 karakter',
                'price.required'        => 'Harga produk harus di isi',
                'price.numeric'         => 'Harga produk hanya boleh angka',
                'price.digits_between'  => 'Harga produk tidak boleh lebih dari 9 digit',
                'sku.max'               => 'SKU produk tidak boleh lebih dari 15 karakter',
                'plu.max'               => 'PLU produk tidak boleh lebih dari 15 karakter',
                'capital.numeric'       => 'Harga modal produk hanya boleh angka',
                'capital.digits_between'=> 'Harga modal produk tidak boleh lebih dari 9 digit',
                // 'image_product.image'   => 'Foto produk hanya bisa mengupload foto saja'
            );

            $validator = Validator::make(request()->all(), [
                'name'                  => 'required|string|max:40',
                'price'                 => 'required|numeric|digits_between:1,9',
                'sku'                   => 'max:15',
                'plu'                   => 'max:15',
                'capital'               => 'sometimes|nullable|numeric|digits_between:1,9',
                // 'image_product'         => 'image|mimes:jpg,jpeg,png|max:2048'
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
            //     ->where('owner_id', auth()->user()->id)
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
            //     ->where('owner_id', auth()->user()->id)
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
                // update to table
                $findProduct = DB::table('products')->where('id', request()->id)->first();
                
                if ($findProduct != null) {
                    $log = new LogController();
                    
                    if (request()->image_product != null) {
                        $image = Image::read(request()->file('image_product'));
                        $imageInfo = pathinfo(request()->file('image_product')->getClientOriginalName());
                        $imageName = time().'.'.$imageInfo['extension'];
                        $destinationPath = public_path('images/products/');
                        $image->save($destinationPath.$imageName, 75);
                    }
                    
                    // for show hide product on cashier
                    $placesId = null;
                    if ($findProduct->active_by_placeid == null) {
                        if (request()->show_product == true) {
                            $placesId = auth()->user()->place_id;
                        } else {
                            $placesId = null;
                        }
                    } else {
                        $changeToArray = explode(',', $findProduct->active_by_placeid);
                        if (in_array(auth()->user()->place_id, $changeToArray)) {
                            if (request()->show_product == "true" || request()->show_product == true) {
                                $placesId = $findProduct->active_by_placeid;
                            } else {
                                if (count($changeToArray) > 1) {
                                    $placesId = implode(',', array_diff($changeToArray, [auth()->user()->place_id]));
                                } else {
                                    $placesId = null;
                                }
                            }
                        } else {
                            if (request()->show_product == true) {
                                $changeToArray[] = auth()->user()->place_id;
                            }
                            $placesId = implode(',', $changeToArray);
                        }
                    }

                    DB::table('products')
                        ->where('id', request()->id)
                        ->update([
                            'owner_id'              => request()->owner_id,
                            'product_category_id'   => request()->product_category_id,
                            'name'                  => request()->name,
                            'price'                 => request()->price,
                            'image_product'         => request()->image_product != null ? $imageName : null,
                            'sku'                   => request()->sku,
                            'plu'                   => request()->plu,
                            'capital'               => request()->capital,
                            'description'           => request()->description,
                            'active'                => request()->active,
                            'active_by_placeid'     => $placesId,
                            'updated_at'            => Carbon::now()->toDateTimeString(),
                        ]);
                    
                    // ini cek image ada atau tida. jika ada dan maka di image di hapus
                    if ($findProduct->image_product != null || $findProduct->image_product != "") {
                        $image_path = public_path()."/images/products/".$findProduct->image_product;
                        if(File::exists($image_path)) {
                            File::delete($image_path);
                        }
                    }
                    
                    // additional detail product
                    // $findAdditionalDetailProduct = DB::table('additional_detail_product')->where('place_id', auth()->user()->place_id) ->where('product_id', request()->id)->first();
                    // if ($findAdditionalDetailProduct != null) {
                    //     DB::table('additional_detail_product')
                    //         ->where('place_id', auth()->user()->place_id)
                    //         ->where('product_id', request()->id)
                    //         ->update([
                    //             'active'
                    //         ]);
                    // } else {
                    //     DB::table('additional_detail_product')->insert([
                    //         'product_id'    =>  request()->id,
                    //         'place_id'      =>  auth()->user()->place_id,
                    //         'active'        =>  request()->show_product == "true" || request()->show_product == true ? 1 : 0,
                    //         'stock'         =>  null,
                    //     ]);
                    // }

                    $log->store("products", 2, request()->id, auth()->user()->id, Carbon::now()->toDateTimeString());
                } else {
                    return response()->json([
                        'status'    =>  false,
                        'error'     =>  'ERROR_NOT_FOUND',
                        'message'   =>  'Produk tidak ditemukan',
                    ], 422);
                }
            } else {
                // insert to table
                $routeName = Route::currentRouteName();
                if ($routeName == "create-product") {
                    DB::table('products')->insert([
                        'owner_id'              => request()->owner_id,
                        'product_category_id'   => request()->product_category_id,
                        'name'                  => request()->name,
                        'price'                 => request()->price,
                        'image_product'         => request()->image_product,
                        'sku'                   => request()->sku,
                        'plu'                   => request()->plu,
                        'capital'               => request()->capital,
                        'description'           => request()->description,
                        'active'                => null,
                        'active_by_placeid'     => request()->show_product ? auth()->user()->place_id : null,
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
            $message = request()->id > 0 ? "Produk berhasil diubah" : "Produk berhasil disimpan";
            return response()->json([
                'status'    =>  true,
                'message'   =>  request()->id > 0 ? "Produk berhasil diubah" : "Produk berhasil disimpan"
            ], 201);
        } else {
            $getPlace = DB::table('place')->where("id", auth()->user()->place_id)->first();
            $getProducts = DB::table('products')->where("owner_id", $getPlace->owner_id)->orderBy('id', 'desc')->get();
            $message = $getProducts->count() > 0 ? "Berhasil mendapatkan semua produk" : "Data produk kosong";
            return response()->json([
                'status'    =>  true,
                'message'   =>  $getProducts->count() > 0 ? "Berhasil mendapatkan semua produk" : "Data produk kosong",
                'data'      =>  $getProducts
            ], 201);
        }
    }

    public function delete() {  // /v1/delete-product
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
