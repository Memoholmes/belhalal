<?php
/**
* CreditWalletEngine.php - Main component file
*
* This file is part of the Credit Wallet User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User;

use Auth;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Illuminate\Support\Arr;
use App\Yantrana\Base\BaseEngine;
use Imdhemy\Purchases\Facades\Product as InAppProduct;
use App\Yantrana\Components\User\Models\loginLogsModel;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Components\User\Models\CreditWalletTransaction;
use App\Yantrana\Components\User\Repositories\LoginLogsRepository;
use App\Yantrana\Components\User\Repositories\ManageUserRepository;
use App\Yantrana\Components\User\Repositories\CreditWalletRepository;
use Imdhemy\GooglePlay\Products\ProductPurchase as InAppProductPrice;
use App\Yantrana\Components\Configuration\Repositories\ConfigurationRepository;
use App\Yantrana\Components\CreditPackage\Repositories\CreditPackageRepository;
use App\Yantrana\Components\FinancialTransaction\Repositories\FinancialTransactionRepository;
use PushBroadcast;

class CreditWalletEngine extends BaseEngine
{
    /**
     * @var  CreditWalletRepository - CreditWallet Repository
     */
    protected $creditWalletRepository;

    /**
     * @var ManageUserRepository - Manage User Repository
     */
    protected $manageUserRepository;

    /**
     * @var  ConfigurationRepository - Configuration Repository
     */
    protected $configurationRepository;

    /**
     * @var PaypalEngine - Paypal Engine
     */
    protected $paypalEngine;

    /**
     * @var StripeEngine - Stripe Engine
     */
    protected $stripeEngine;

    /**
     * @var  CreditPackageRepository - CreditPackage Repository
     */
    protected $creditPackageRepository;

    /**
     * @var RazorpayEngine - Razorpay Engine
     */
    protected $razorpayEngine;

    /**
     * @var coinGateEngine - coinGate Engine
     */
    protected $coinGateEngine;

    /**
     * @var razorpayAPI - razorpayAPI
     */
    protected $razorpayAPI;

    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var LoginLogsRepository - loginLogs Repository
     */
    protected $loginLogsRepository;
     /**
     * @var CryptoEngine - CryptoEngine Engine
     */
    protected $cryptoEngine;
    /**
     *
     * @var financialTransactionRepository - financialTransactionRepository
     */
    protected $financialTransactionRepository;

    /**
     * Constructor
     *
     * @param  CreditWalletRepository  $creditWalletRepository - CreditWallet Repository
     * @param  ManageUserRepository  $manageUserRepository - Manage User Repository
     * @param  ConfigurationRepository  $configurationRepository - Configuration 
     * Repository
      * @param FinancialTransactionRepository - financialTransactionRepository

     * @param  PaypalEngine  $paypalEngine- Paypal Engine
     * @param  StripeEngine  $stripeEngine- Stripe Engine
     * @param  CryptoEngine  $cryptoEngine- CryptoEngine Engine
     * @param  CreditPackageRepository  $creditPackageRepository - CreditPackage Repository
     * @param  RazorpayEngine  $razorpayEngine - Razorpay Repository
     * @param  CoinGateEngine  $coinGateEngine - coinGate Engine
     * @param  UserRepository  $userRepository - user Repository
     * @param  LoginLogsRepository  $loginLogsRepository - loginLogs Repository
     * @return  void
     *-----------------------------------------------------------------------*/
    public function  __construct(
        CreditWalletRepository $creditWalletRepository,
        ManageUserRepository $manageUserRepository,
        ConfigurationRepository $configurationRepository,
        PaypalEngine $paypalEngine,
        StripeEngine $stripeEngine,
        CryptoEngine $cryptoEngine,
        CreditPackageRepository $creditPackageRepository,
        RazorpayEngine $razorpayEngine,
        CoinGateEngine $coinGateEngine,
        UserRepository $userRepository,
        LoginLogsRepository $loginLogsRepository,
        FinancialTransactionRepository $financialTransactionRepository,
    ) {
        $this->creditWalletRepository = $creditWalletRepository;
        $this->financialTransactionRepository = $financialTransactionRepository;
        $this->manageUserRepository = $manageUserRepository;
        $this->configurationRepository = $configurationRepository;
        $this->paypalEngine = $paypalEngine;
        $this->stripeEngine = $stripeEngine;
        $this->cryptoEngine = $cryptoEngine;
        $this->creditPackageRepository = $creditPackageRepository;
        $this->razorpayEngine = $razorpayEngine;
        $this->coinGateEngine = $coinGateEngine;
        $this->userRepository = $userRepository;
        $this->loginLogsRepository = $loginLogsRepository;
    }
    public function initSetup()
    {
        if (getStoreSettings('use_test_razorpay')) {
            $razorpayKey = getStoreSettings('razorpay_testing_key');
            $razorpaySecret = getStoreSettings('razorpay_testing_secret_key');
        } else {
            $razorpayKey = getStoreSettings('razorpay_live_key');
            $razorpaySecret = getStoreSettings('razorpay_live_secret_key');
        }
        $this->razorpayAPI = new Api($razorpayKey, $razorpaySecret);
    }

    /**
     * Prepare Credit Wallet User Data.
     *
     *
     *---------------------------------------------------------------- */
    public function prepareCreditWalletUserData()
    {
        //get credit package data
        $packageCollection = $this->creditPackageRepository->fetchAllActiveCreditPackage();

        $creditPackages = [];
        // check if user collection exists
        if (! __isEmpty($packageCollection)) {
            foreach ($packageCollection as $key => $package) {
                $packageImageUrl = '';
                $packageImageFolderPath = getPathByKey('package_image', ['{_uid}' => $package->_uid]);
                $packageImageUrl = getMediaUrl($packageImageFolderPath, $package['image']);
                $creditPackages[] = [
                    '_id' => $package['_id'],
                    '_uid' => $package['_uid'],
                    'package_uid' => toggleProductId($package['_uid']),
                    'package_name' => $package['title'],
                    'credit' => $package['credits'],
                    'price' => intval($package['price']),
                    'packageImageUrl' => $packageImageUrl,
                ];
            }
        }

        return $this->engineReaction(1, [
            'creditWalletData' => [
                'creditPackages' => $creditPackages,
            ],
            'paymentData' => [
                'currencySymbol' => getStoreSettings('currency_symbol'),
                'currency' => getStoreSettings('currency'),
                'enablePaypalCheckout' => getStoreSettings('enable_paypal'),
                'useTestPaypalCheckout' => getStoreSettings('use_test_paypal_checkout'),
                'paypalTestingClientId' => getStoreSettings('paypal_checkout_testing_client_id'),
                'paypalLiveClientId' => getStoreSettings('paypal_checkout_live_client_id'),
                'userName' => getUserAuthInfo('profile.full_name'),
                'userEmail' => getUserAuthInfo('profile.email'),
                'enableRazorpay' => getStoreSettings('enable_razorpay'),
                'useTestRazorpay' => getStoreSettings('use_test_razorpay'),
                'razorpayTestKey' => getStoreSettings('razorpay_testing_key'),
                'razorpayLiveKey' => getStoreSettings('razorpay_live_key'),
                'enableStripe' => getStoreSettings('enable_stripe'),
                'useTestStripe' => getStoreSettings('use_test_stripe'),
                'stripeTestPublishableKey' => getStoreSettings('stripe_testing_publishable_key'),
                'stripeLivePublishableKey' => getStoreSettings('stripe_live_publishable_key'),
            ],
        ]);
    }

    /**
     * Prepare Credit wallet Information
     *
     * @return void
     */
    public function prepareCreditWalletInfo()
    {
        return $this->engineReaction(1, [
            'creditBalance' => totalUserCredits()
        ]);
    }

    /**
     * get user transaction list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareUserWalletTransactionList()
    {
        $transactionCollection = $this->creditWalletRepository->fetchUserWalletTransactionList();

        $requireColumns = [
            '_id',
            '_uid',
            'created_at' => function ($key) {
                return formatDate($key['created_at']);
            },
            'credits',
            'credit_type',
            'transactionType' => function ($key) {
                $type = null;
                if (! __isEmpty($key['get_user_financial_transaction'])) {
                    $type = 1;
                } elseif (! __isEmpty($key['get_user_gift_transaction'])) {
                    $type = 2;
                } elseif (! __isEmpty($key['get_user_sticker_transaction'])) {
                    $type = 3;
                } elseif (! __isEmpty($key['get_user_boost_transaction'])) {
                    $type = 4;
                } elseif (! __isEmpty($key['get_user_subscription_transaction'])) {
                    $type = 5;
                } elseif ($key['credit_type'] == 1) {
                    $type = 6;
                }

                return $type;
            },
            'formattedTransactionType' => function ($key) {
                $type = null;
                if (! __isEmpty($key['get_user_financial_transaction'])) {
                    $type = 1;
                } elseif (! __isEmpty($key['get_user_gift_transaction'])) {
                    $type = 2;
                } elseif (! __isEmpty($key['get_user_sticker_transaction'])) {
                    $type = 3;
                } elseif (! __isEmpty($key['get_user_boost_transaction'])) {
                    $type = 4;
                } elseif (! __isEmpty($key['get_user_subscription_transaction'])) {
                    $type = 5;
                } elseif ($key['credit_type'] == 1) {
                    $type = 6;
                }

                return isset($type) ? configItem('user_transaction_type', $type) : null;
            },
            'financialTransactionDetail' => function ($key) {
                $financialTransaction = [];
                if (! __isEmpty($key['get_user_financial_transaction'])) {
                    $transactionData = $key['get_user_financial_transaction'];
                    $financialTransaction = [
                        '_id' => $transactionData['_id'],
                        '_uid' => $transactionData['_uid'],
                        'status' => configItem('payments.status_codes', $transactionData['status']),
                        'amount' => priceFormat($transactionData['amount'], true, false),
                        'created_at' => formatDate($transactionData['created_at']),
                        'currency_code' => $transactionData['currency_code'],
                        'payment_mode' => configItem('payments.payment_checkout_modes', $transactionData['is_test']),
                        'method' => $transactionData['method'],
                    ];
                }

                return $financialTransaction;
            },
        ];

        return $this->dataTableResponse($transactionCollection, $requireColumns);
    }

    /**
     * get api user transaction list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function apiCreditWalletTransactionList()
    {
        $transactionCollection = $this->creditWalletRepository->fetchApiUserWalletTransactionList();

        $requireColumns = [
            '_id',
            '_uid',
            'created_at' => function ($key) {
                return formatDate($key['created_at']);
            },
            'credits',
            'credit_type',
            'transactionType' => function ($key) {
                $type = null;
                if (! __isEmpty($key['get_user_financial_transaction'])) {
                    $type = 1;
                } elseif (! __isEmpty($key['get_user_gift_transaction'])) {
                    $type = 2;
                } elseif (! __isEmpty($key['get_user_sticker_transaction'])) {
                    $type = 3;
                } elseif (! __isEmpty($key['get_user_boost_transaction'])) {
                    $type = 4;
                } elseif (! __isEmpty($key['get_user_subscription_transaction'])) {
                    $type = 5;
                } elseif ($key['credit_type'] == 1) {
                    $type = 6;
                }

                return $type;
            },
            'formattedTransactionType' => function ($key) {
                $type = null;
                if (! __isEmpty($key['get_user_financial_transaction'])) {
                    $type = 1;
                } elseif (! __isEmpty($key['get_user_gift_transaction'])) {
                    $type = 2;
                } elseif (! __isEmpty($key['get_user_sticker_transaction'])) {
                    $type = 3;
                } elseif (! __isEmpty($key['get_user_boost_transaction'])) {
                    $type = 4;
                } elseif (! __isEmpty($key['get_user_subscription_transaction'])) {
                    $type = 5;
                } elseif ($key['credit_type'] == 1) {
                    $type = 6;
                }

                return isset($type) ? configItem('user_transaction_type', $type) : null;
            },
            'financialTransactionDetail' => function ($key) {
                $financialTransaction = [];
                if (! __isEmpty($key['get_user_financial_transaction'])) {
                    $transactionData = $key['get_user_financial_transaction'];
                    $financialTransaction = [
                        '_id' => $transactionData['_id'],
                        '_uid' => $transactionData['_uid'],
                        'status' => configItem('payments.status_codes', $transactionData['status']),
                        'amount' => priceFormat($transactionData['amount'], true, false),
                        'created_at' => formatDate($transactionData['created_at']),
                        'currency_code' => $transactionData['currency_code'],
                        'payment_mode' => configItem('payments.payment_checkout_modes', $transactionData['is_test']),
                        'method' => $transactionData['method'],
                    ];
                }

                return $financialTransaction;
            },
        ];

        return $this->customTableResponse($transactionCollection, $requireColumns);
    }

    /**
     * Process paypal complete transaction.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processPaypalTransaction($inputData, $packageUid)
    {
        if ($this->creditWalletRepository->isAlreadyProcessed($inputData['id'])) {
            return $this->engineReaction(2, null,  __tr('Already been processed'));
        }
        // process card charge
        $paypalPaymentDetail = $this->paypalEngine->getOrder($inputData['id']);

        //check reaction code is 1 or not
        if ($paypalPaymentDetail['reaction_code'] == 1) {
            $paypalResponse = $paypalPaymentDetail['data']['transactionResponse'];

            //check transaction status is completed or not
            if ($paypalResponse['status'] == 'COMPLETED') {
                //store transaction data
                if ($this->storePaymentData($paypalResponse, $packageUid, 'paypalPayment')) {
                    return $this->engineReaction(1, null,  __tr('Payment Complete'));
                }
            } else {
                //payment failed response
                return $this->engineReaction(2, null,  __tr('Payment Failed'));
            }
        } else {
            //error response
            return $this->engineReaction(2, [
                'errorMessage' => 'Something went wrong, please contact system administrator',
            ],  __tr('Payment Failed'));
        }
    }

    /**
     * Process paypal complete transaction.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processPaypalApiTransaction($inputData, $packageUid)
    {
        // process card charge
        $paypalPaymentData = $this->paypalEngine->ApiCapturePaypalTransaction($inputData['id']);

        //check reaction code is 1 or not
        if ($paypalPaymentData['reaction_code'] == 1) {
            $paymentStatus = array_get($paypalPaymentData, 'data.transactionDetail.payer.status');
            $state = $paypalPaymentData['data']['transactionDetail']['state'];
            $paypalResponse = $paypalPaymentData['data']['transactionDetail'];

            if($this->creditWalletRepository->isAlreadyProcessed($paypalResponse['id'])) {
                return $this->engineReaction(2, null, __tr('Already been processed'));
            }

            //check transaction status is completed or not
            if ($paymentStatus == 'VERIFIED' and $state == 'approved') {
                //store transaction data
                if ($this->storePaymentData($paypalResponse, $packageUid, 'apiPaypalPayment')) {
                    return $this->engineReaction(1, null,  __tr('Payment Complete'));
                }
            } else {
                //payment failed response
                return $this->engineReaction(2, null,  __tr('Payment Failed'));
            }
        } else {
            //error response
            return $this->engineReaction(2, [
                'errorMessage' => 'Something went wrong, please contact system administrator',
            ],  __tr('Payment Failed'));
        }
    }

    /**
     * Process Payment request
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processPayment($inputData)
    {
        $paymentMethod = $inputData['select_payment_method'];
        $packageUid = $inputData['select_package'];

        //get package collection
        $packageCollection = $this->creditPackageRepository->fetch($packageUid);

        //if it is empty then throw error
        if ( __isEmpty($packageCollection)) {
            //success function
            return $this->engineReaction(2, null,  __tr('Package does not exist.'));
        }

        //check payment method and package data exists
        if ($paymentMethod == 'stripe') {
            $packageImageFolderPath = getPathByKey('package_image', ['{_uid}' => $packageCollection->_uid]);
            $packageImageUrl = getMediaUrl($packageImageFolderPath, $packageCollection['image']);

            $stripeRequestData = [
                'packageUid' => $packageUid,
                'package_name' => $packageCollection['title'],
                'amount' => $packageCollection['price'],
                'currency' => getStoreSettings('currency'),
                'packageImageUrl' => $packageImageUrl,
                'userId'=> getUserId(),
            ];
            //check is mobile app request
            if (isMobileAppRequest()) {
                $stripeRequestData['redirectAppUrl'] = base64_encode($inputData['redirectAppUrl']);
            }

            //get stripe session ata
            $stripeSessionData = $this->stripeEngine->processStripeRequest($stripeRequestData);

            //if reaction code is 1 then success response
            if ($stripeSessionData['reaction_code'] == 1) {
                return $this->engineReaction(1, [
                    'stripeSessionData' => $stripeSessionData['data'],
                ],  __tr('Success'));
            } else {
                //stripe failure response
                return $this->engineReaction(2, [
                    'errorMessage' => $stripeSessionData['data']['errorMessage'],
                ],  __tr('Failed'));
            }
        }

        //failure response
        return $this->engineReaction(2, null,  __tr('Something went wrong, please contact to system administrator'));
    }

    /**
     * Process retrieve stripe payment data
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function prepareStripeRetrieveData($inputData)
    {
        //get stripe payment ata
        $stripePaymentData = $this->stripeEngine->retrieveStripeData($inputData['session_id']);
        //check reaction code is 1
        if ($stripePaymentData['reaction_code'] == 1) {
            $stripeData = $stripePaymentData['data']['paymentData'];

            if ($this->creditWalletRepository->isAlreadyProcessed($stripeData['id'])) {
                return $this->engineReaction(1, null,  __tr('Already been processed'));
            }

            //store transaction data
            if ($this->storeStripePaymentData($stripeData, $inputData['packageUid'])) {
                return $this->engineReaction(1, null,  __tr('Payment Complete'));
            } else {
                //payment failed response
                return $this->engineReaction(2, null,  __tr('Payment Failed'));
            }
        }
        //failure response
        return $this->engineReaction(2, null,  __tr('Payment failed.'));
    }

    /**
     * Process paypal complete transaction.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function storeStripePaymentData($inputData, $packageUid)
    {
        //get package collection
        $packageCollection = $this->creditPackageRepository->fetch($packageUid);
        //if it is empty then throw error
        if ( __isEmpty($packageCollection)) {
            //success function
            return $this->engineReaction(2, null,  __tr('Package does not exist.'));
        }

        if (! __isEmpty($inputData)) {
            $isStripeTestMode = 1;
            if (!getStoreSettings('use_test_stripe')) {
                $isStripeTestMode = 2;
            }
            //collect store data
            $storeData = [
                'status' => 2,
                'amount' => $inputData['amount'] / 100,
                'users__id' => $inputData['metadata']['userId'],
                'method' => configItem('payments.payment_methods', 2),
                'currency_code' => getStoreSettings('currency'),
                'is_test' => $isStripeTestMode,
                'txn_id' => array_get($inputData, 'id'),
                '__data' => [
                    'rawPaymentData' => json_encode($inputData),
                    'packageName' => $packageCollection['title'],
                ],
            ];
            //store transaction process
            if ($this->creditWalletRepository->storeTransaction($storeData, $packageCollection)) {
                //success function
                return $this->engineReaction(1, null,  __tr('Purchase successfully'));
            }
        }
       
        //error response
        return $this->engineReaction(2, null,  __tr('Purchased failed'));
    }

    /**
     * Process paypal complete transaction.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function processRazorpayCheckout($inputData)
    {
        // process card charge
        $razorpayChargeRequest = $this->razorpayEngine->capturePayment($inputData['razorpayPaymentId']);

        //check reaction code is 1 or not
        if ($razorpayChargeRequest['reaction_code'] == 1) {
            $razorpayResponse = $razorpayChargeRequest['data']['transactionDetail'];

            if ($this->creditWalletRepository->isAlreadyProcessed($razorpayResponse['id'])) {
                return $this->engineReaction(2, null,  __tr('Already been processed'));
            }

            //check transaction status is completed or not
            if ($razorpayResponse['captured'] === true) {
                //store transaction data
                if ($this->storePaymentData($razorpayResponse, $inputData['packageUid'], 'razorpayPayment')) {
                    return $this->engineReaction(1, null,  __tr('Payment Complete'));
                }
            } else {
                //payment failed response
                return $this->engineReaction(2, null,  __tr('Payment Failed'));
            }
        } else {
            //error response
            return $this->engineReaction(2, [
                'errorMessage' => 'Something went wrong, please contact system administrator',
            ],  __tr('Payment Failed'));
        }
    }

    /**
     * Process paypal complete transaction.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function storePaymentData($inputData, $packageUid, $paymentMethod, $userId = null)
    {
        //get package collection
        $packageCollection = $this->creditPackageRepository->fetch($packageUid);

        //if it is empty then throw error
        if ( __isEmpty($packageCollection)) {
            //success function
            return $this->engineReaction(2, null,  __tr('Package does not exist.'));
        }

        // check if user collection exists
        if (! __isEmpty($inputData)) {
            $isTestMode = 1;
            $amount = $packageCollection['price'];
            $currency = getStoreSettings('currency');
            $paymentType = null;
            //collect paypal payment data
            if ($paymentMethod == 'paypalPayment') {
                $paymentType = configItem('payments.payment_methods', 1);
                //check is live mode
                if (!getStoreSettings('use_test_paypal_checkout')) {
                    $isTestMode = 2;
                }

                //collect razorpay payment data
            } elseif ($paymentMethod == 'razorpayPayment') {
                $paymentType = configItem('payments.payment_methods', 3);
                //check is live mode
                if (!getStoreSettings('use_test_razorpay')) {
                    $isTestMode = 2;
                }
            } elseif ($paymentMethod == 'apiPaypalPayment') {
                $paymentType = configItem('payments.payment_methods', 4);
                //check is live mode
                if (!getStoreSettings('use_test_paypal_checkout')) {
                    $isTestMode = 2;
                }
            }
            if($userId == null){
                $userId = getUserID();
            }
            //collect store data
            $storeData = [
                'status' => 2, //completed
                'amount' => $amount,
                'users__id' => $userId,
                'method' => $paymentType,
                'currency_code' => $currency,
                'is_test' => $isTestMode,
                'txn_id' => array_get($inputData, 'id'),
                '__data' => [
                    'rawPaymentData' => json_encode($inputData),
                    'packageName' => $packageCollection['title'],
                ],
            ];

            //store transaction process
            if ($financialTransactionId = $this->creditWalletRepository->storeTransaction($storeData, $packageCollection, $userId)) {
                //fetch updated user total credits by helper function
                totalUserCredits();
                //success function
                return $this->engineReaction(1, null,  __tr('Purchase successfully'));
            }
        }

        return $this->engineReaction(2, null,  __tr('Purchased failed'));
    }

    /**
     * Prepare Credit Wallet Stripe Intent User Data.
     *
     *
     *---------------------------------------------------------------- */
    public function processCreateStripePaymentIntent($inputData)
    {
        //get package collection
        $packageCollection = $this->creditPackageRepository->fetch($inputData['packageUid']);

        //if it is empty then throw error
        if ( __isEmpty($packageCollection)) {
            //success function
            return $this->engineReaction(2, null,  __tr('Package does not exist.'));
        }

        //get stripe payment intent data
        $stripePaymentIntentData = $this->stripeEngine->createPaymentIntent($packageCollection, $inputData['paymentMethodId']);

        if ($stripePaymentIntentData['reaction_code'] == 1) {
            return $this->engineReaction(1, $stripePaymentIntentData);
        }

        return $this->engineReaction(2, $stripePaymentIntentData);
    }

    /**
     * Prepare Credit Wallet Stripe Intent User Data.
     *
     *
     *---------------------------------------------------------------- */
    public function retrieveStripePaymentIntent($inputData)
    {
        //get package collection
        $packageCollection = $this->creditPackageRepository->fetch($inputData['packageUid']);

        //if it is empty then throw error
        if ( __isEmpty($packageCollection)) {
            //success function
            return $this->engineReaction(2, null,  __tr('Package does not exist.'));
        }

        //get stripe payment intent data
        $retrievePaymentIntentData = $this->stripeEngine->retrievePaymentIntent($packageCollection, $inputData['paymentIntentId']);

        if ($retrievePaymentIntentData['reaction_code'] == 1) {
            return $this->engineReaction(1, $retrievePaymentIntentData);
        }

        return $this->engineReaction(2, $retrievePaymentIntentData);
    }

    public function processInAppPurchase($request)
    {
        try {
            //code...
        $productReceipt = InAppProduct::googlePlay()->id($request->get('productId'))->token($request->get('purchaseToken'))->get();
        // $packageUid = $productReceipt->getProductId();
        $purchaseState = $productReceipt->getPurchaseState();
        $orderId = $productReceipt->getOrderId();
        if($purchaseState != 0) {
            return $this->engineReaction(2, null,  __tr('Purchase not complete'));
        }
         //get package collection
         $packageCollection = $this->creditPackageRepository->fetch(toggleProductId($request->get('productId')));

            if ($this->creditWalletRepository->isAlreadyProcessed($orderId)) {
                return $this->engineReaction(2, null,  __tr('Already been processed'));
            }

            if (! __isEmpty($packageCollection)) {
                //collect store data
                $storeData = [
                    'status' => 2,
                    'amount' => $packageCollection->price,
                    'users__id' => getUserID(),
                    'method' => configItem('payments.payment_methods', 5),
                    'currency_code' => getStoreSettings('currency'),
                    'is_test' => configItem('payments.in_app_test_mode'),
                    'txn_id' => $orderId,
                    ' __data' => [
                        'rawPaymentData' => json_encode($productReceipt->toArray()),
                        'packageName' => $packageCollection->title,
                    ],
                ];

                //store transaction process
                if ($this->creditWalletRepository->storeTransaction($storeData, $packageCollection)) {
                    //success function
                    return $this->engineReaction(1, null,  __tr('Purchase successfully'));
                }
            }
            //error response
            return $this->engineReaction(2, null,  __tr('Transaction not completed'));
        } catch (\Exception $e) {
             __pr($e->getMessage());
            //throw $th;
            return $this->engineReaction(2, null,  __tr('Transaction not completed'));
        }
    }


    public function processCoinGateCheckout($inputData)
    {
        $packageCollection = $this->creditPackageRepository->fetch($inputData['packageUid']);

        if ( __isEmpty($packageCollection)) {
            //success function
            return $this->engineReaction(2, null,  __tr('Package does not exist.'));
        }

        $isCoingateTestMode = 1;
        if (!getStoreSettings('use_test_coingate')) {
            $isCoingateTestMode = 2;
        }

        $storeData = [
            'status' => 4,
            'amount' => $inputData['amount'],
            'users__id' => getUserID(),
            'method' => configItem('payments.payment_methods', 6),
            'currency_code' => getStoreSettings('currency'),
            'is_test' => $isCoingateTestMode,
            'txn_id' => null,
            '__data' => [
                'rawPaymentData' => json_encode($inputData),
                'packageName' => $inputData['packageName'],
            ],
        ];

        //made new function for getting order id
        if ($orderData = $this->coinGateEngine->storeTransaction($storeData, $packageCollection)) {
            //success function
            $coinGateResponse = $this->coinGateEngine->processCoinGateCheckout($inputData, $orderData);
            if ($coinGateResponse['reaction_code'] == 1) {
                return $this->engineReaction(1, $coinGateResponse);
            }
            return $this->engineReaction(2, null,  __tr('Something went wrong'));
        }
        //error response
        return $this->engineReaction(2, null,  __tr('Transaction not completed'));
    }

    /**
     * Coingate Callback Data
     *
     * @return  object
     */
    public function coingateCallbackData()
    {
        return $this->coinGateEngine->captureCoinGateData();
    }

    /**
     * Handle Order Payment Stripe Webhook
     *
     * @return  json message
     */
    public function handleOrderPaymentStripeWebhook()
    {
        $paymentWebhookData = $this->stripeEngine->paymentWebhook();
        if ($paymentWebhookData['reaction_code'] == 1) {
            //check reaction code is 1
            if (isset($paymentWebhookData['data']['paymentIntent'])) {
                // Access the 'paymentIntent' key safely
                $stripeData = $paymentWebhookData['data']['paymentIntent'];
                if ($stripeData['status'] == 'succeeded') {
                    if ($this->creditWalletRepository->isAlreadyProcessed($stripeData['id'])) {
                        return $this->engineReaction(2, null,  __tr('Already been processed'));
                    }
                    //store transaction data
                    if ($this->storeStripePaymentData($stripeData, $stripeData['metadata']['packageUid'])) {
                        return $this->engineReaction(1, null,  __tr('Payment Complete'));
                    } else {
                        //payment failed response
                        return $this->engineReaction(2, null,  __tr('Payment Failed'));
                    }
                }
            } else {
                //failure response
            return $this->engineReaction(2, null,  $paymentWebhookData['data']['paymentIntent']);
            }

        }
        return $this->engineResponse(2, [], __tr('Payment Fail'));
    }

    /**
     * Handle Order Payment RazorPay Webhook
     *
     * @return
     */
    public function handleOrderPaymentRazorPayWebhook()
    {
        $paymentWebhookData = $this->razorpayEngine->paymentWebhook();

        if ($paymentWebhookData['reaction_code'] == 1) {

            $razorpayResponse = $paymentWebhookData['data']['paymentIntent'];
            $paymentWebhookRazorPayData = $razorpayResponse['payload']['payment']['entity'];
            $paymentIntentId = $paymentWebhookRazorPayData['id'];

            if ($this->creditWalletRepository->isAlreadyProcessed($paymentIntentId)) {
                return $this->engineReaction(2, null,  __tr('Already been processed'), 200);
            }

            if ($paymentWebhookRazorPayData['captured'] === true) {
                //store transaction data
                if ($this->storePaymentData($paymentWebhookRazorPayData, $paymentWebhookRazorPayData['notes']['packageUid'], 'razorpayPayment', $paymentWebhookRazorPayData['notes']['userId'])) {
                    return $this->engineReaction(1, null,  __tr('Payment Complete'), 200);
                }
            } else {
                //payment failed response
                return $this->engineReaction(2, null,  __tr('Payment Failed'));
            }
        }
        return $this->engineResponse(2, [], __tr('Payment Fail'));
    }


    /**
     * Process To ShowCreditBonus
     *
     * @return json object
     */
    public function processToUpdateLog()
    {
        $loginLogs = loginLogsModel::where('user_id', getUserID())->first();
        $this->loginLogsRepository->updateLoginLogs($loginLogs, ['updated_at' => Carbon::now()]);

        return $this->engineResponse(1, null);
    }


    /**
     * Prepare Order Process
     *
     * @param   array  $inputData
     *
     * @return  json object
     */
    public function prepareOrderProcess($inputData)
    {
        $packageCollection = $this->creditPackageRepository->fetch($inputData['packageUid']);

        if ( __isEmpty($packageCollection)) {
            //success function
            return $this->engineReaction(2, null,  __tr('Package does not exist.'));
        }

        $isPaypalCheckoutTestMode = 1;
        if (!getStoreSettings('use_test_paypal_checkout')) {
            $isPaypalCheckoutTestMode = 2;
        }

        $storeData = [
            'status' => 4,
            'amount' => $inputData['packagePrice'],
            'users__id' => getUserID(),
            'method' => configItem('payments.payment_methods', 4),
            'currency_code' => getStoreSettings('currency'),
            'is_test' => $isPaypalCheckoutTestMode,
            'txn_id' => null,
            '__data' => [
                'rawPaymentData' => json_encode($inputData),
                'packageName' => $inputData['packageName'],
            ],
        ];

        //made new function for getting order id
        if ($orderData = $this->coinGateEngine->storeTransaction($storeData, $packageCollection)) {
            //success function
            $paypalResponse = $this->paypalEngine->paypalOrderCreate($inputData, $orderData);
          

            if ($paypalResponse['reaction_code'] == 1) {
                return $this->engineReaction(1, $paypalResponse['data']);
            }
            return $this->engineReaction(2, null,  __tr('Something went wrong'));
        }
        //error response
        return $this->engineReaction(2, null,  __tr('Transaction not store'));
    }

    /**
     * Process Capture Paypal Order
     *
     * @param   array  $inputData
     *
     * @return  json   object
     */
    public function processCapturePaypalOrder($inputData)
    {
       return $this->paypalEngine->paypalCaptureOrder($inputData);
    }

    /////crypto
     /**
     * Process Capture crypto Order
     *
     * @param   array  $inputData
     *
     * @return  json   object
     */
    public function processCryptoCheckout($inputData)
    {
       
        $packageUID=$inputData['metadata']['package_id'];
        $packageName=$inputData['metadata']['package_name'];
        $userID=$inputData['metadata']['customer_id'];
        if($inputData['data']['id']) {
            $paymentTxnId = $inputData['data']['id'];
        } else
         {
            $paymentTxnId = $inputData['id'];
        } 

        if ($this->creditWalletRepository->isAlreadyProcessed($paymentTxnId)) {

            return $this->engineReaction(2, null,  __tr('Already been processed'), 200);
        }
        //fetch package data
        $packageCollection = $this->creditPackageRepository->fetch($packageUID);
       //check package is available
        if ( __isEmpty($packageCollection)) {
            //success function
            return $this->engineReaction(2, null,  __tr('Package does not exist.'));
        }
        //crypto test mode
        $isCryptoTestMode = 1;
        //fetch crypto test keys
        if (!getStoreSettings('use_test_crypto')) {
            $isCryptoTestMode = 2;
        }
        //connvert package amount
        $packageAmount=($inputData['amount']/100);
        //prepare transaction storedata
        $storeData = [
            'status' => 4,
            'amount' => $packageAmount,
            'users__id' => $userID,
            'method' => configItem('payments.payment_methods', 7),
            'currency_code' => getStoreSettings('currency'),
            'is_test' => $isCryptoTestMode,
            'txn_id' => null,
            '__data' => [
                'rawPaymentData' => json_encode($inputData),
                'packageName' => $packageName,
            ],
        ];

        //made new function for getting order id
        if ($orderData = $this->cryptoEngine->storeTransaction($storeData, $packageCollection)) {
           
            //success function
            $cryptoResponseData = $this->cryptoEngine->processCryptoStoreData($inputData, $orderData,$paymentTxnId);

            if ($cryptoResponseData['reaction_code'] == 1) {
                return $this->engineReaction(1, $cryptoResponseData);
            }
            return $this->engineReaction(2, null,  __tr('Something went wrong'));
        }
        //error response
        return $this->engineReaction(2, null,  __tr('Transaction not completed'));
    }

     /**
     * Handle Order Payment crypto Webhook
     *
     * @return  json message
     */
    public function handleOrderPaymentCryptoWebhook()
    {
         //fetch webhook data
        $paymentWebhookData = $this->cryptoEngine->paymentWebhook();

        if ($paymentWebhookData['reaction_code'] == 1) {
             //prepare webhook data
            $cryptoResponse = $paymentWebhookData['data']['paymentIntent'];
            $paymentTxnId = $cryptoResponse['id'];
              //check is transaction already done
                if ($this->creditWalletRepository->isAlreadyProcessed($paymentTxnId)) {

                return $this->engineReaction(2, null,  __tr('Already been processed'), 200);
            }

              //if txn id not found
            if ($cryptoResponse['status'] === 'succeeded') {
                //store transaction data
                if ($this->processCryptoCheckout($cryptoResponse)) {
                    return $this->engineReaction(1, null,  __tr('Payment Complete'), 200);
                }
            } else {
                //payment failed response
                return $this->engineReaction(2, null,  __tr('Payment Failed'));
            }
        }
        return $this->engineResponse(2, [], __tr('Payment Fail'));
    }

  

}
