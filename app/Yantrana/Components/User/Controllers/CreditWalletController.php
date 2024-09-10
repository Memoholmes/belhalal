<?php
/**
* CreditWalletController.php - Controller file
*
* This file is part of the Credit Wallet User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\User\CreditWalletEngine;
use App\Yantrana\Components\User\Requests\PaymentProcessRequest;
use App\Yantrana\Components\User\Requests\PaypalTransactionRequest;

// form Requests

class CreditWalletController extends BaseController
{
    /**
     * @var  CreditWalletEngine - CreditWallet Engine
     */
    protected $creditWalletEngine;

    /**
     * Constructor
     *
     * @param  CreditWalletEngine  $creditWalletEngine - CreditWallet Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(CreditWalletEngine $creditWalletEngine)
    {
        $this->creditWalletEngine = $creditWalletEngine;
    }

    /**
     * Manage Credit Wallet List.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function creditWalletView()
    {
        $processReaction = $this->creditWalletEngine->prepareCreditWalletUserData();

        return $this->loadPublicView('user.credit-wallet.credit-wallet', $processReaction['data']);
    }

    /**
     * Show user Transaction List.
     *
     *-----------------------------------------------------------------------*/
    public function getUserWalletTransactionList()
    {
        return $this->creditWalletEngine->prepareUserWalletTransactionList();
    }

    /**
     * Handle complete transaction request.
     *
     * @param  PaypalTransactionRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function paypalTransactionComplete(PaypalTransactionRequest $request, $packageUid)
    {
        $processReaction = $this->creditWalletEngine->processPaypalTransaction($request->all(), $packageUid);

        //check reaction code equal to 1
        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Handle complete transaction request.
     *
     * @param  PaymentProcessRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function paymentProcess(PaymentProcessRequest $request)
    {
        $processReaction = $this->creditWalletEngine->processPayment($request->all());

        //check reaction code equal to 1
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle complete transaction request.
     *
     * @param  PaymentProcessRequest  $request
     * @return json response
     *---------------------------------------------------------------- */
    public function stripeCallbackUrl(PaymentProcessRequest $request)
    {
        $processReaction = $this->creditWalletEngine->prepareStripeRetrieveData($request->all());
        //check reaction code is 1
        if ($processReaction['reaction_code'] == 1) {
            return redirect()->route('user.credit_wallet.read.view')->with([
                'success' => true,
                'message' => __tr('Payment successfully.'),
            ]);
        } else {
            return redirect()->route('user.credit_wallet.read.view')->with([
                'error' => true,
                'message' => __tr('Payment failed.'),
            ]);
        }
    }

    /**
     * Thanks page.
     *
     * @return json response
     *---------------------------------------------------------------- */
    public function stripeCancelCallback()
    {
        return redirect()->route('user.credit_wallet.read.view')->with([
            'error' => true,
            'message' => __tr('Payment failed.'),
        ]);
    }

    /**
     * Razorpay Checkout
     *
     * @param  string  $orderUid
     * @return json response
     *---------------------------------------------------------------- */
    public function razorpayCheckout(PaypalTransactionRequest $request)
    {
        $processReaction = $this->creditWalletEngine->processRazorpayCheckout($request->all());

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Razorpay Checkout
     *
     * @param  string  $orderUid
     * @return json response
     *---------------------------------------------------------------- */
    public function coingateCheckout(PaypalTransactionRequest $request)
    {
       
        $processReaction = $this->creditWalletEngine->processCoinGateCheckout($request->all());

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Coingate Url
     *
     * @param   int  $status
     *
     * @return  json response
     */
    public function coingateSuccessOrFailUrl($status)
    {
        //check reaction code is 1
        if ($status == 'success') {
            return redirect()->route('user.credit_wallet.read.view')->with([
                'success' => true,
                'message' => __tr('Payment successfully.'),
            ]);
        } else {
            return redirect()->route('user.credit_wallet.read.view')->with([
                'error' => true,
                'message' => __tr('Payment failed.'),
            ]);
        }
    }

    /**
     * Coingate Callback Url
     *
     * @return  json response
     */
    public function coingateCallbackUrl()
    {
        $processReaction = $this->creditWalletEngine->coingateCallbackData();
        //check reaction code is 1
        if ($processReaction['reaction_code'] == 1) {
            return redirect()->route('user.credit_wallet.read.view')->with([
                'success' => true,
                'message' => __tr('Payment successfully.'),
            ]);
        } else {
            return redirect()->route('user.credit_wallet.read.view')->with([
                'error' => true,
                'message' => __tr('Payment failed.'),
            ]);
        }
    }

    /**
     * handle Order Payment Stripe Webhook
     *
     * @return
     */
    public function handleOrderPaymentStripeWebhook()
    {
        return $this->creditWalletEngine->handleOrderPaymentStripeWebhook();
    }

    /**
     * Handle Razorpay Payment Webhook
     *
     * @return json object
     */
    public function handleOrderPaymentRazorpayWebhook()
    {
        return $this->creditWalletEngine->handleOrderPaymentRazorPayWebhook();
    }

    /**
     * Show Credit Bonus
     *
     * @return  json object
     */
    public function updateLog()
    {
        return $this->creditWalletEngine->processToUpdateLog();
    }

    /**
     * order submit process.
     *
     * @param OrderProcessRequest $request
     *
     * @return json response
     *---------------------------------------------------------------- */
    public function submitPaypalOrderProcess(PaymentProcessRequest $request)
    {
       
        $processReaction = $this->creditWalletEngine->prepareOrderProcess($request->all());
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Process Capture Paypal Order
     *
     * @param   object  $request
     *
     * @return  json   object
     */
    public function capturePaypalOrder(PaymentProcessRequest $request)
    {
        $processReaction = $this->creditWalletEngine->processCapturePaypalOrder($request->all());
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Wallet Transactions View
     *
     */
    public function walletTransactionsView()
    {
        return $this->loadPublicView('user.credit-wallet.wallet-transaction'); 
    }

    //crypto
    public function cryptoPaymentProcess(PaymentProcessRequest $request)
    {
  
        //check response for crypto payment
         $processReaction = $this->creditWalletEngine->processCryptoCheckout($request->all());

         return $this->responseAction(
             $this->processResponse($processReaction, [], [], true)
         );
        

    }
    
    /**
     * handle Order Payment Stripe Webhook
     *
     * @return
     */
    public function handleOrderPaymentCryptoWebhook()
    {
        //handle webhook data
       $processReaction = $this->creditWalletEngine->handleOrderPaymentCryptoWebhook();
        return $this->processResponse($processReaction, [], [], true);
    }
}
