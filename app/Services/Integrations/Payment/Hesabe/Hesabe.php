<?php

namespace App\Services\Integrations\Payment\Hesabe;

use App\Services\Integrations\Payment\Hesabe\Libraries\HesabeCrypt;
use App\Services\Integrations\Payment\Hesabe\Misc\PaymentHandler;
use App\Services\Integrations\Payment\Hesabe\Models\HesabeCheckoutRequestModel;
use App\Services\Orders\Orders;
use App\Order;
use Illuminate\Support\Facades\Http;
use App\Services\Integrations\Payment\Constants\PaymentStatus;

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

    protected function getPaymentInfo(string $orderUid)
    {
        $url = env('HESABE_PAYMENT_API_URL') . "/api/transaction/". $orderUid ."?isOrderReference=1";
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'accessCode' => env('HESABE_ACCESS_CODE')
        ])->get($url);
        return $response->json();
    }

    public function paymentProcessAfterRedirect(Order $order, string $data, bool $status): int
    {
        $decryptResponse = $this->hesabeCrypt::decrypt($data, $this->secretKey, $this->ivKey);
        $decryptResponse = json_decode($decryptResponse, true);
        $decryptResponse = $decryptResponse['response'];
        // Success
        if ($status) {
            (new Orders)->OrderPaymentSuccess($order->uid, $decryptResponse['transactionId']);
            return PaymentStatus::SUCCESS;
        }
        // Failure
        if (!$status) {
            (new Orders)->OrderPaymentFailure($order->uid);
            return PaymentStatus::FAILURE;
        }
    }

    public function paymentsProcessByOrderUid(string $orderUid): int
    {
        $paymentInfo = $this->getPaymentInfo($orderUid);
        // Still pending not processd by user
        if (empty($paymentInfo['data'])) {
            return PaymentStatus::PENDING;
        }

        // Success Case
        if ($paymentInfo['data']['status'] == "SUCCESSFUL") {
            (new Orders)->OrderPaymentSuccess($orderUid, $paymentInfo['data']['TransactionID']);
            return PaymentStatus::SUCCESS;
        }

        // Failure Case
        if ($paymentInfo['data']['status'] == "FAILED") {
            (new Orders)->OrderPaymentFailure($orderUid);
            return PaymentStatus::FAILURE;
        }
    }
}