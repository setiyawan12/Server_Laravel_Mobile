<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Transaksi;
use App\TransaksiDetail;
use App\User;

class TransaksiController extends Controller
{
    public function store (Request $requset){
        $validasi = Validator::make($requset->all(),[
            'user_id' => 'required',
            'total_item' => 'required',
            'total_harga' => 'required',
            'name' => 'required',
            'jasa_pengiriaman' => 'required',
            'ongkir' => 'required',
            'total_transfer' => 'required',
            'bank' => 'required',
            'phone' => 'required'
        ]);
        if($validasi->fails()){
            $val = $validasi->error()->all();
            return $this->error($val[0]);
        }
        $kode_payment = "INV/PYM/" . now()->format('Y-m-d') . "/" . rand(100, 999);
        $kode_trx = "INV/PYM/" . now()->format('Y-m-d') . "/" . rand(100, 999);
        $kode_unik = rand(100, 999);
        $status = "MENUNGGU";
        $expired_at = now()->addDay();

        $dataTransaksi = array_merge($requset->all(), [
            'kode_payment' => $kode_payment,
            'kode_trx' => $kode_trx,
            'kode_unik' => $kode_unik,
            'status' => $status,
            'expired_at' => $expired_at
        ]);

        \DB::beginTransaction();
        $transaksi = Transaksi::create($dataTransaksi);
        foreach ($requset->produks as $produk) {
            $detail = [
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk['id'],
                'total_item' => $produk['total_item'],
                'catatan' => $produk['catatan'],
                'total_harga' => $produk['total_harga']
            ];
            $transaksiDetail = TransaksiDetail::create($detail);
        }
        if (!empty($transaksi) && !empty($transaksiDetail)) {
            \DB::commit();
            return response()->json([
                'success' => 1,
                'message' => 'Transaksi Berhasil',
                'transaksi' => collect($transaksi)
            ]);
        } else {
            \DB::rollback();
            return $this->error('Transaksi gagal');
        }
    }
    public function history($id) {
        $transaksis = Transaksi::with(['user'])->whereHas('user', function ($query) use ($id) {
            $query->whereId($id);
        })->orderBy("id", "desc")->get();

        foreach ($transaksis as $transaksi) {
            $details = $transaksi->details;
            foreach ($details as $detail) {
                $detail->produk;
            }
        }

        if (!empty($transaksis)) {
            return response()->json([
                'success' => 1,
                'message' => 'Transaksi Berhasil',
                'transaksis' => collect($transaksis)
            ]);
        } else {
            $this->error('Transaksi gagal');
        }
    }
    public function batal($id){
        $transaksi = Transaksi::with(['details.produk'])->where('id', $id)->first();
        if ($transaksi){
            // update data

            $transaksi->update([
                'status' => "BATAL"
            ]);
            $this->Notif("transaksi di batalkan","transaksi produk ".$transaksi->details[0]->produk->name." berhasil di batalkan");

            return response()->json([
                'success' => 1,
                'message' => 'Berhasil',
                'transaksi' => $transaksi
            ]);
        } else {
            return $this->error('Gagal memuat transaksi');
        }
    }
    public function Notif($title,$message) {
        // $mData = [
        //     'title' => "TEST TITLE",
        //     'body' => "HASIL BODY"
        // ];
        $mData = [
            'title' => $title,
            'body' => $message
        ];

        $fcm[] = "eLEusdJ1RY2LXe5i9N7Eee:APA91bG-HbN3vlcF3s3vXwu0OYtlTtW6EtUVXmg7NkWZIKhX7Qyn549SBiCJp3hpefk15xvA2Y-BR3sSMbaQdjT-rOixYhsJbLoYNDTZB0yqqVw3vTPb53hdE_QOPLrtEfgZWp-98Qpr";

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
    public function error($pesan) {
        return response()->json([
            'success' => 0,
            'message' => $pesan
        ]);
    }
}
