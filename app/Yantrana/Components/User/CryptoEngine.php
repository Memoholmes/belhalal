<?php

namespace App\Yantrana\Components\User;

use App\Yantrana\Base\BaseEngine;
use Hamcrest\Core\IsSame;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Yantrana\Components\User\Models\CreditWalletTransaction;
use App\Yantrana\Components\FinancialTransaction\Models\FinancialTransaction;
use App\Yantrana\Components\FinancialTransaction\Repositories\FinancialTransactionRepository;
use App\Yantrana\Components\CreditPackage\Repositories\CreditPackageRepository;
use App\Yantrana\Components\User\Repositories\CreditWalletRepository;


class CryptoEngine extends BaseEngine
{
    /**
     * @var  string $webhookSecret - stripe webhook secret
     */
    protected $webhookSecret;
    protected $initSetup;

    /**
     * @var  CreditPackageRepository - CreditPackage Repository
     */
    protected $creditPackageRepository;

    /**
     * @var  CreditWalletRepository - CreditWallet Repository
     */
    protected $creditWalletRepository;

    /**
     * @var paymentUrl - paymentUrl
     */
    protected $paymentUrl;
    /**
     *
     * @var financialTransactionRepository - financialTransactionRepository
     */
    protected $financialTransactionRepository;

    /**
     * @var cryptoToken - coingateToken
     */
    protected $cryptoToken;
    /**
     * @var cryptoToken - coingateToken
     */
    protected $cryptoPaymentUrl;
     /**
     * @var cryptoToken - coingateToken
     */
    protected $cryptoSecretKey;

    /**
     * Constructor.
     * @param  CreditPackageRepository  $creditPackageRepository - CreditPackage Repository
     * @param  CreditWalletRepository  $creditWalletRepository - CreditWallet Repository
     * @param FinancialTransactionRepository - financialTransactionRepository
     *
     *-----------------------------------------------------------------------*/
    public function __construct(FinancialTransactionRepository $financialTransactionRepository, CreditWalletRepository $creditWalletRepository,CreditPackageRepository $creditPackageRepository)
    {
        if (getStoreSettings('use_test_crypto')) {
            $this->cryptoToken = getStoreSettings('crypto_testing_publishable_key');
            $this->cryptoPaymentUrl = "https://pay.crypto.com/sdk/payments";
            $this->webhookSecret = getStoreSettings('crypto_testing_webhook_secret');
            $this->cryptoSecretKey = getStoreSettings('crypto_testing_secret_key');

        } else {
            $this->cryptoToken = getStoreSettings('crypto_live_publishable_key');
            $this->cryptoPaymentUrl = "https://pay.crypto.com/sdk/payments";
            $this->webhookSecret = getStoreSettings('stripe_live_webhook_secret');
            $this->cryptoSecretKey = getStoreSettings('crypto_live_secret_key');
        }

        $this->financialTransactionRepository = $financialTransactionRepository;
        $this->creditPackageRepository = $creditPackageRepository;
        $this->creditWalletRepository = $creditWalletRepository;
    }

 /**
     * This method use for retrieve crypto payment.
     *
     * @param  string  $orderId
     *
     *---------------------------------------------------------------- */

    public function processCryptoStoreData($inputData,$orderId,$paymentTxnId)
     {
        
        $packageName=$inputData['metadata']['package_name'];
        $cryptoOrderUrl="https://js.crypto.com/sdk/payments/checkout/set_wallet?publishableKey=".$this->cryptoToken ;
       
        //prepare amount
        $amount = ($inputData['amount']/100);

        try {
            $response = Http::acceptJson()->withHeaders([
                'Authorization' => 'Bearer ' . $this->cryptoSecretKey,
                "Content-Type" => "application/json",
                "Accept" => "application/json",
                
            ])->post($cryptoOrderUrl, [
                "order_id" => $orderId,
                "title" => $packageName,
                "price_amount" => $amount,
                "price_currency" => getStoreSettings('currency'),
                "receive_currency" => getStoreSettings('currency'),
                "status" =>"succeeded",

            ]);

                     if (!__isEmpty($inputData) && $inputData['status'] == 'succeeded') {

                        //if it is exist then throw error
                      $financialTransactionCollection = $this->financialTransactionRepository->fetch($orderId);

                      //if it is empty then throw error
                      if (__isEmpty($financialTransactionCollection)) {
                      //success function
                      return $this->engineReaction(2, null, __tr('Package does not exist.'));
                       }

                      $data = $financialTransactionCollection->toArray();
                      $rawPaymentData = json_decode($data['__data']['rawPaymentData'], true);
                   
                     //collect package collection data
                      $packageCollection = $this->creditPackageRepository->fetch($rawPaymentData['metadata']['package_id']);
                     //prepared update data
                      $updateData = [
                      'txn_id' => $paymentTxnId,
                      'status' => 2,
                      '__data' => [
                     'rawPaymentData' => json_encode($data),
                     'packageName' => $packageName,
                     ],
                   ];
                    //update transaction process
                  if ($this->financialTransactionRepository->updateIt($financialTransactionCollection, $updateData)) {
                  $this->creditWalletRepository->storeCredits([
                  'userId' => $financialTransactionCollection['users__id'],
                  'credits' => $packageCollection->credits,
                  'txnId' => $financialTransactionCollection->_id
                  ]);
                  //success function
                    return $this->engineReaction(1, null, __tr('Purchase successfully'));
                  }
   
               }
                 return $this->engineReaction(2, null, __tr('Payment failed'));
              }
               catch (\Exception $ex) {
               $error =  $ex->getMessage();
              dd($error);
           }

    }

/**
     * This method use for generateAccessToken.
     *
     * 
     *
     *---------------------------------------------------------------- */
    public function generateAccessToken()
    {
        $data = [
            'grant_type' => 'client_credentials',
        ];

        $accessToken = Http::asForm()->withBasicAuth($this->cryptoToken,)
            ->post("$this->cryptoPaymentUrl", $data);

        return $accessToken->json('access_token');
    }
    /**
     * This method use for store Transaction.
     *
     * 
     *@param  string  $inputData
     *---------------------------------------------------------------- */
    public function storeTransaction($inputData, $packageData)
    {
        $keyValues = [
            'status',
            'amount',
            'users__id',
            'method',
            'currency_code',
            'is_test',
            'txn_id',
            '__data',
        ];

        $financialTransaction = new FinancialTransaction;

        // Check if new User added
        if ($financialTransaction->assignInputsAndSave($inputData, $keyValues)) {

                return $financialTransaction->_id;
        }

        return false;   // on failed
    }

   
     /**
     * Payment Webhook
     *
     * @return  json object
     */
    public function paymentWebhook()
    {
        
      $payload = request()->all();
      
      $paymentIntent = null;
  
     switch ($payload['type']) {
        case 'payment.captured':
          $paymentIntent = $payload['data']['object']; // contains a crypto data
          break;

        default:
          echo 'Received unknown event type ' . $payload['type'];
      }


    http_response_code(200);
      return $this->engineReaction(1, ['paymentIntent' => $paymentIntent]);
  
    }

 /**
     * Payment Webhook
     *
     * @return  json object
     */
    //check valid signature
    public function isValidSignature($payload,  $secret)
{
    $expectedSignature = hash_hmac('sha256', $payload, $secret);

    return hash_equals($expectedSignature, $sig_header);
}


}


