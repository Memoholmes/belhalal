<?php

namespace App\Services\Integrations\Payment\Hesabe;

use App\Services\Integrations\Payment\Hesabe\Libraries\HesabeCrypt;
use App\Services\Integrations\Payment\Hesabe\Misc\PaymentHandler;
use App\Services\Integrations\Payment\Hesabe\Models\HesabeCheckoutRequestModel;
use App\Services\Orders\Orders;
use App\Order;

class Hesabe
{
    public $paymentApiUrl;
    public $secretKey;
    public $ivKey;
    public $accessCode;
    public $hesabeCheckoutResponseModel;
    public $modelBindingHelper;
    public $hesabeCrypt;
    public $merchantCode;

    public function __construct()
    {

        $this->paymentApiUrl = env('HESABE_PAYMENT_API_URL');
        // Get all three values from Merchant Panel, Profile section
        $this->secretKey = env('HESABE_MERCHANT_SECRET_KEY'); // Use Secret key
        $this->merchantCode = (int) env('HESABE_MERCHANT_CODE'); // Use Merchant Code
        $this->ivKey = env('HESABE_MERCHANT_IV'); // Use Iv Key
        $this->accessCode = env('HESABE_ACCESS_CODE'); // Use Access Code
        // $this->hesabeCheckoutResponseModel = new HesabeCheckoutResponseModel();
        // $this->modelBindingHelper = new ModelBindingHelper();
        $this->hesabeCrypt = new HesabeCrypt();   // instance of Hesabe Crypt library
    }

    public function makePaymentUrl(HesabeCheckoutRequestModel $requestData)
    {
        $paymentHandler = new PaymentHandler($this->paymentApiUrl, $this->merchantCode, $this->secretKey, $this->ivKey, $this->accessCode);
        $response = $paymentHandler->checkoutRequest($requestData);
        $decryptResponse = $this->hesabeCrypt::decrypt($response, $this->secretKey, $this->ivKey);
        $decryptResponse = json_decode($decryptResponse, true);
        $responseToken = $decryptResponse['response']['data'];
        $baseUrl = "https://sandbox.hesabe.com/payment";
        return $baseUrl . '?data='. $responseToken;
    }

    public function paymentProcess(Order $order, string $data)
    {
        $decryptResponse = $this->hesabeCrypt::decrypt($data, $this->secretKey, $this->ivKey);
        $decryptResponse = json_decode($decryptResponse, true);
        $decryptResponse = $decryptResponse['response'];
        if ($decryptResponse['resultCode'] == "ACCEPT") {
            // Success
            dd($decryptResponse);
            return (new Orders)->OrderPaymentSuccess($order->uid, $decryptResponse['transactionId']);
        }
        if ($decryptResponse['resultCode'] != "ACCEPT") {
            // Failure
            dd($decryptResponse);
            return (new Orders)->OrderPaymentFailure($order->uid);
        }
    }
}