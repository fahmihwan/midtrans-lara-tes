<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function payment(Request $request)
    {
        // Set your Merchant Server Key
        // \Midtrans\Config::$serverKey = 'SB-Mid-server-l4ja6huIEGPSjPi5oGsTKesl';
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = false;
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;

        $params = array(
            'transaction_details' => array(
                'order_id' => rand(),
                'gross_amount' => 1000,
            ),
            'item_details' => array(
                [
                    "id" => "a01",
                    "price" => 500,
                    "quantity" => 1,
                    "name" => "Apple"
                ],
                [
                    "id" => "a01",
                    "price" => 500,
                    "quantity" => 1,
                    "name" => "Apple"
                ],

            ),
            'customer_details' => array(
                'first_name' => $request->nama,
                'last_name' => '',
                'email' => $request->email,
                'phone' => $request->telp,
            ),
        );

        $snapToken = \Midtrans\Snap::getSnapToken($params);
        return view('payment', [
            'snap_token' => $snapToken
        ]);
    }

    public function payment_post(Request $request)
    {
        $json = json_decode($request->json);

        $orderModel = new Order();
        $orderModel->status = $json->transaction_status;
        $orderModel->name = $request->name;
        $orderModel->email = $request->email;
        $orderModel->number = $request->number;
        $orderModel->transaction_id = $json->transaction_id;
        $orderModel->order_id = $json->order_id;
        $orderModel->gross_amount = $json->gross_amount;
        $orderModel->payment_type = $json->payment_type;
        $orderModel->payment_code = isset($json->payment_code) ? $json->payment_code : null;
        $orderModel->pdf_url = isset($json->pdf_url) ? $json->pdf_url : null;

        // dd($orderModel);
        $orderModel->save();
        return redirect('/')->with('success', $json->transaction_status);
    }
}
