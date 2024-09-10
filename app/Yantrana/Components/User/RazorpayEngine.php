<?php

namespace App\Yantrana\Components\User;

use App\Yantrana\Base\BaseEngine;
use Razorpay\Api\Api as RazorpayAPI;

/**
 * This MailService class for manage globally -
 * mail service in application.
 *---------------------------------------------------------------- */
class RazorpayEngine extends BaseEngine
{
    protected $razorpayAPI;
    protected $webhookSecret;
    /**
     * Constructor.
     *
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
        //check razorpay test mode is on
        if (getStoreSettings('use_test_razorpay')) {
            $razorpayKey = getStoreSettings('razorpay_testing_key');
            $razorpaySecret = getStoreSettings('razorpay_testing_secret_key');
            $this->webhookSecret = getStoreSettings('razorpay_testing_webhook_secret');
        } else {
            $razorpayKey = getStoreSettings('razorpay_live_key');
            $razorpaySecret = getStoreSettings('razorpay_live_secret_key');
            $this->webhookSecret = getStoreSettings('stripe_live_webhook_secret');
        }
        $this->razorpayAPI = new RazorpayAPI($razorpayKey, $razorpaySecret);
    }

    /**
     * This method use for capturing payment.
     *
     * @param  string  $paymentId
     * @return paymentReceived
     *---------------------------------------------------------------- */
    public function capturePayment($paymentId)
    {
        try {
            // fetch a particular payment
            $payment = $this->razorpayAPI->payment->fetch($paymentId);

            // Captures a payment
            $paymentReceived = $this->razorpayAPI->payment->fetch($paymentId)->capture(['amount' => $payment['amount']]);

            return $this->engineReaction(1, [
                'transactionDetail' => $paymentReceived->toArray(),
            ], __tr('Complete'));
        } catch (\Exception $e) {
            return $this->engineReaction(2, [
                'errorMessage' => 'Invalid Api Key',
            ], $e->getMessage());
        }
    }

    public function paymentWebhook()
    {
        $this->razorpayAPI;

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
        $event = $paymentIntent = null;

        try {
            $this->razorpayAPI->utility->verifyWebhookSignature($payload, $sig_header, $this->webhookSecret);
            $event = json_decode($payload, true);
        } catch (\Errors\SignatureVerificationError $e) {
            __logDebug($e->getMessage());
            // Invalid signature
            // http_response_code(400);
            http_response_code(400);
            exit();
        }

        switch ($event['event']) {
            case 'payment.captured':
                $paymentIntent = $event;
                break;

            default:
                echo 'Received unknown event type ' . $event['event'];
                break;
        }
        http_response_code(200);
        return $this->engineReaction(1, ['paymentIntent' => $paymentIntent]);
    }
}
