<?php

namespace App\Services\Integrations\Payment\Hesabe\Models;

use App\Order;

/**
 * This class contains all parameters that needs to be defined
 * before encrypting and passing to the checkout api
 *
 * @author Hesabe
 */
class HesabeCheckoutRequestModel
{
    public $amount;
    public $currency;
    public $paymentType = 0;
    public $orderReferenceNumber;
    public $version = "2.0";
    public $merchantCode;
    public $responseUrl;
    public $failureUrl;
    public $webhookUrl;


    public function __construct(Order $order)
    {
        $this->currency = $order->currency_code;
        $this->amount = (float) $order->amount;
        $this->merchantCode = env('HESABE_MERCHANT_CODE');
        $this->responseUrl = $order->successUrl();
        $this->failureUrl = $order->failureUrl();
        $this->webhookUrl = $order->webhookUrl();
        $this->orderReferenceNumber = $order->uid;
    }
}
