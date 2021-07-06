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
        $transaksi = Transaksi::with(['details.produk','user'])->where('id', $id)->first();
        $this->Notif("transaksi di batalkan","transaksi produk ".$transaksi->details[0]->produk->name." berhasil di batalkan", $transaksi->user->fcm);
        $transaksi->update([
            'status' => "BATAL"
        ]);
        return redirect('transaksi');
    }

    public function confirm($id){
        $transaksi = Transaksi::with(['details.produk','user'])->where('id', $id)->first();
        $this->Notif("transaksi di proses","transaksi produk ".$transaksi->details[0]->produk->name." sedang di proses", $transaksi->user->fcm);
        $transaksi->update([
            'status' => "PROSES"
        ]);
        return redirect('transaksi');
    }

    public function kirim($id){
        $transaksi = Transaksi::with(['details.produk','user'])->where('id', $id)->first();
        $this->Notif("transaksi di kirim","transaksi produk ".$transaksi->details[0]->produk->name." sedang di kirim", $transaksi->user->fcm);
        $transaksi->update([
            'status' => "DIKIRIM"
        ]);
        return redirect('transaksi');
    }

    public function selesai($id){
        $transaksi = Transaksi::with(['details.produk','user'])->where('id', $id)->first();
        $this->Notif("transaksi selesai","transaksi produk ".$transaksi->details[0]->produk->name." berhasil di kirim ke tujuan", $transaksi->user->fcm);
        $transaksi->update([
            'status' => "SELESAI"
        ]);
        return redirect('transaksi');
    }
    public function Notif($title,$message,$mfcm) {
        // $mData = [
        //     'title' => "TEST TITLE",
        //     'body' => "HASIL BODY"
        // ];
        $mData = [
            'title' => $title,
            'body' => $message
        ];

        $fcm[] = $mfcm;

        $payload = [
            'registration_ids' => $fcm,
            'notification' => $mData
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Content-type: application/json",
                "Authorization: key=AAAApHQCZGs:APA91bHL3_-iAj7zTfKvvO6DxTbKBlHoXejY_fsqdPnDpeF9eqc4azKiwolwbC88U0dbBgXMrctrR4_FqA4lujHKABddlem4tCvS5CurfToLRZaStJsMOT6e1KCjlls1QWOKeX2dq7hr"
            ),
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = [
            'success' => 1,
            'message' => "Push notif success",
            'data' => $mData,
            'firebase_response' => json_decode($response)
        ];
        return $data;
    }
}
