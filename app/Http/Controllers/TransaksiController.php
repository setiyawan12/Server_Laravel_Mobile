<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaksi;
class TransaksiController extends Controller
{
    public function index(){
        $transaksiPading['listPanding'] = Transaksi::whereStatus("MENUNGGU")->get();

        $transaksiSelesai['listDone'] = Transaksi::where("status", "NOT LIKE", "%MENUNGGU%")->get();

        return view('transaksi')->with($transaksiPading)->with($transaksiSelesai);
    }
    public function batal($id){
        $transaksi = Transaksi::where('id', $id)->first();
        $transaksi->update([
            'status' => "BATAL"
        ]);
        return redirect('transaksi');
    }

    public function confirm($id){
        $transaksi = Transaksi::where('id', $id)->first();
        $transaksi->update([
            'status' => "PROSES"
        ]);
        return redirect('transaksi');
    }

    public function kirim($id){
        $transaksi = Transaksi::where('id', $id)->first();
        $transaksi->update([
            'status' => "DIKIRIM"
        ]);
        return redirect('transaksi');
    }

    public function selesai($id){
        $transaksi = Transaksi::where('id', $id)->first();
        $transaksi->update([
            'status' => "SELESAI"
        ]);
        return redirect('transaksi');
    }
}
