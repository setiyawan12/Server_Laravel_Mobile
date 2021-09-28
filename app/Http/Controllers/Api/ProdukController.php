<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Produk;

class ProdukController extends Controller
{
    public function index(){
        $produk = Produk::all();
        return response()->json(
            [
                'success' => true,
                'message' => 'Get Produk Berhasil',
                'data' => $produk
            ]
            );
    }
}
