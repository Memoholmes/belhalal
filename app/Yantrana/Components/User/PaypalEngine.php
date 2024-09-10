<?php

namespace App\Yantrana\Components\User;

use App\Yantrana\Base\BaseEngine;
use Exception;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Illuminate\Support\Facades\Http;
use App\Yantrana\Components\FinancialTransaction\Repositories\FinancialTransactionRepository;
use App\Yantrana\Components\CreditPackage\Repositories\CreditPackageRepository;
use App\Yantrana\Components\User\Repositories\CreditWalletRepository;

/**
 * This PaypalEngine class for manage globally -
 * mail service in application.
 *---------------------------------------------------------------- */
class PaypalEngine extends BaseEngine
{
    protected $paypalAPI;
    protected $apiContext;
    protected $paypalCheckoutUrl;
    protected $paypalKey;
    protected $paypalSecret;

     /**
     * @var  CreditPackageRepository - CreditPackage Repository
     */
    protected $creditPackageRepository;

    /**
     * @var  CreditWalletRepository - CreditWallet Repository
     */
    protected $creditWalletRepository;

    /**
     *
     * @var financialTransactionRepository - financialTransactionRepository
     */
    protected $financialTransactionRepository;
    /**
     * Constructor.
     * @param  CreditPackageRepository  $creditPackageRepository - CreditPackage Repository
     * @param  CreditWalletRepository  $creditWalletRepository - CreditWallet Repository
     *-----------------------------------------------------------------------*/
    public function __construct(FinancialTransactionRepository $financialTransactionRepository, CreditWalletRepository $creditWalletRepository,CreditPackageRepository $creditPackageRepository)
    {
        /**
         * Set up and return PayPal PHP SDK environment with PayPal access credentials.
         * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
         */
        if (getStoreSettings('use_test_paypal_checkout')) {
            $clientId = getStoreSettings('paypal_checkout_testing_client_id');
            $clientSecret = getStoreSettings('paypal_checkout_testing_secret_key');
            $paypalCheckoutUrl = config('__tech.paypal_checkout_urls.sandbox');
        } else {
            $clientId = getStoreSettings('paypal_checkout_live_client_id');
            $clientSecret = getStoreSettings('paypal_checkout_live_secret_key');
            $paypalCheckoutUrl = config('__tech.paypal_checkout_urls.production');
        }

        // $this->paypalAPI = new PayPalHttpClient($environment);

        /** @var \Paypal\Rest\ApiContext $apiContext */
        $this->apiContext = $this->getApiContext($clientId, $clientSecret);
        $this->paypalKey = $clientId;
        $this->paypalSecret = $clientSecret;
        $this->paypalCheckoutUrl = $paypalCheckoutUrl;

        $this->financialTransactionRepository = $financialTransactionRepository;
        $this->creditPackageRepository = $creditPackageRepository;
        $this->creditWalletRepository = $creditWalletRepository;
    }

    /**
     * This method use for get payment details
     * 2. Set up your server to receive a call from the client
     * 3. Call PayPal to get the transaction details
     *
     * @param  string  $paymentId
     * You can use this function to retrieve an order by passing order ID as an argument.
     * @return paymentReceived
     *---------------------------------------------------------------- */
    public function getOrder($paymentId)
    {
        //try if it is success else throw error
        try {
            //get capture order request
            $request = new OrdersGetRequest($paymentId);

            //execute request for payment response
            $response = $this->paypalAPI->execute($request);

            //success reaction
            return $this->engineReaction(1, [
                'transactionResponse' => json_decode(json_encode($response->result), true),
            ], __tr('Complete'));
        } catch (Exception $e) {
            //failure response with message
            return $this->engineReaction(2, [
                'errorMessage' => $e->getMessage(),
            ], __tr('Failed'));
        }
    }

    /**
     * @param  string  $order Data - Order ID
     * @param  string -$stripeToken - Stripe Token

     * request to Stripe checkout
     *---------------------------------------------------------------- */
    public function ApiCapturePaypalTransaction($paypalPaymentId)
    {
        //try payment successful
        // try {
        //     // Call API with your client and get a response for your call
        //     // $response = $client->execute($request);
        //     $response = Payment::get($paypalPaymentId, $this->apiContext);

        //     // If call returns body in response, you can get the deserialized version from the result attribute of the response
        //     return $this->engineReaction(1, [
        //         'transactionDetail' => $response->toArray(),
        //     ]);
        // } catch (\PayPal\Exception\PayPalConnectionException $ex) {
        //     // echo $ex->statusCode;
        //     // echo $ex->getCode();
        //     // echo $ex->getData();
        //     // print_r($ex->getMessage());
        //     return $this->engineReaction(2, null, $ex->getData());
        // }
    }

    /**
     * Helper method for getting an APIContext for all calls
     *
     * @param  string  $clientId Client ID
     * @param  string  $clientSecret Client Secret
     * @return PayPal\Rest\ApiContext
     */
    public function getApiContext($clientId, $clientSecret)
    {
        // ### Api context
        // Use an ApiContext object to authenticate
        // API calls. The clientId and clientSecret for the
        // OAuthTokenCredential class can be retrieved from
        // developer.paypal.com

        // $apiContext = new ApiContext(
        //     new OAuthTokenCredential(
        //         $clientId,
        //         $clientSecret
        //     )
        // );

        // return $apiContext;
    }


    /**
     * Generate Access Token
     *------------------------------------------------------*/
    public function generateAccessToken()
    {
        $data = [
            'grant_type' => 'client_credentials',
        ];

        $accessToken = Http::asForm()->withBasicAuth($this->paypalKey, $this->paypalSecret)
            ->post("$this->paypalCheckoutUrl/v1/oauth2/token", $data);

        return $accessToken->json('access_token');
    }


    /**
     * Paypal Order Create
     *
     * @param   mix  $orderUID
     *------------------------------------------------------------------*/
    public function paypalOrderCreate($inputData, $orderUID)
    {
        $paypalCheckoutUrl = "$this->paypalCheckoutUrl/v2/checkout/orders";

        try {
            $accessToken = $this->generateAccessToken();

            $createOrder = [
                "intent" => "CAPTURE",
                "purchase_units" => [[
                    "reference_id" => $orderUID,
                    "amount" => [
                        "currency_code" => getStoreSettings('currency'),
                        "value" => $inputData['packagePrice']
                    ]
                ]],
            ];


            $request = Http::withHeaders([
                'Content-Type' => "application/json",
                'Authorization' => "Bearer $accessToken",
                'PayPal-Request-Id' => $orderUID,
            ])->post($paypalCheckoutUrl, $createOrder);

            return $this->engineReaction(1, ['createPaypalOrder' => json_decode($request->getBody()->getContents())]);
        } catch (\Exception $ex) {
            $error =  $ex->getMessage();
            dd($error);
        }
    }

    public function paypalCaptureOrder($inputData)
    {
        $orderUID = $inputData['orderUID'];
        $accessToken = $this->generateAccessToken();

        $request = Http::withHeaders([
            'Content-Type' => "application/json",
            'Authorization' => "Bearer $accessToken",
            'PayPal-Request-Id' => $orderUID,
        ])->post("$this->paypalCheckoutUrl/v2/checkout/orders/{$orderUID}/capture", $inputData);

        $capturedPaypalData = json_decode($request->getBody()->getContents());

        if (!__isEmpty($capturedPaypalData) && $capturedPaypalData->status == 'COMPLETED') {
            $orderId = $capturedPaypalData->purchase_units[0]->reference_id;

            $financialTransactionCollection = $this->financialTransactionRepository->fetch($orderId);
            //if it is empty then throw error
            if (__isEmpty($financialTransactionCollection)) {
                //success function
                return $this->engineReaction(2, null, __tr('Package does not exist.'));
            }

            $data = $financialTransactionCollection->toArray();
            $rawPaymentData = json_decode($data['__data']['rawPaymentData'], true);
            $packageCollection = $this->creditPackageRepository->fetch($rawPaymentData['packageUid']);

            //collect update data
            $updateData = [
                'txn_id' => $capturedPaypalData->id,
                'status' => 2,
                '__data' => [
                    'rawPaymentData' => json_encode($capturedPaypalData),
                    'packageName' => $data['__data']['packageName'],
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
            return $this->engineReaction(2, null, __tr('Purchased failed'));
        }
        return $this->engineReaction(2, null, __tr('Payment failed'));
    }
}
