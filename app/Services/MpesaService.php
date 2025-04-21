<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected $client;
    protected $baseUrl;
    protected $shortCode;
    protected $passkey;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('mpesa.env') == 'sandbox' 
            ? 'https://sandbox.safaricom.co.ke' 
            : 'https://api.safaricom.co.ke';
        $this->shortCode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
    }

    public function generateAccessToken()
    {
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        $response = $this->client->request('GET', $url, [
            'auth' => [config('mpesa.consumer_key'), config('mpesa.consumer_secret')]
        ]);
        $body = json_decode($response->getBody(), true);

        return $body['access_token'];
    }

    public function stkPush($amount, $phone, $accountReference, $transactionDesc)
    {
        $url = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortCode . $this->passkey . $timestamp);

        $response = $this->client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->generateAccessToken(),
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'BusinessShortCode' => $this->shortCode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $phone,
                'PartyB' => $this->shortCode,
                'PhoneNumber' => $phone,
                'CallBackURL' => route('mpesa.callback'),
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    // Add other M-PESA functionalities like B2C, B2B, C2B, etc.
}
