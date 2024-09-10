<?php
/**
* PremiumPlanEngine.php - Main component file
*
* This file is part of the Premium Plan User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\User\Repositories\{UserRepository};
use App\Yantrana\Support\CommonTrait;
use Carbon\Carbon;

class PremiumPlanEngine extends BaseEngine
{
    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var CommonTrait - Common Trait
     */
    use CommonTrait;

    /**
     * Constructor
     *
     * @param  UserRepository  $userRepository  - User Repository
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Prepare Buy Premium Plan User Data.
     *
     *
     *---------------------------------------------------------------- */
    public function preparePremiumPlanUserData()
    {
        $premiumPlanCollection = getStoreSettings('plan_duration');
        $premiumFeaturesCollection = getStoreSettings('feature_plans');
        $premiumPlans = $premiumPlanData = $premiumFeatureData = [];

        // check if user premium plan exists
        if (! __isEmpty($premiumPlanCollection)) {
            $premiumPlans = is_array($premiumPlanCollection) ? $premiumPlanCollection : json_decode($premiumPlanCollection, true);
            $planDurationConfig = config('__settings.items.premium-plans');
            $defaultPlanDuration = $planDurationConfig['plan_duration']['default'];
            $premiumPlanData = combineArray($defaultPlanDuration, $premiumPlans);
        }

        // check if user premium features exists
        if (! __isEmpty($premiumFeaturesCollection)) {
            $premiumFeature = is_array($premiumFeaturesCollection) ? $premiumFeaturesCollection : json_decode($premiumFeaturesCollection, true);
            // Get settings from config
            $featurePlanConfig = config('__settings.items.premium-feature');
            $defaultFeaturePlans = $featurePlanConfig['feature_plans']['default'];
            $premiumFeatureData = combineArray($defaultFeaturePlans, $premiumFeature);
        }

        $userSubscription = $this->userRepository->fetchUserSubscription();

        $userSubscriptionData = [];
        if (! __isEmpty($userSubscription) and ! __isEmpty($premiumPlans) and ! __isEmpty($premiumPlanData)) {
            $planData = $premiumPlanData[$userSubscription->plan_id];
            $expiryAt = isset($userSubscription->expiry_at) ? formatDate($userSubscription->expiry_at, 'l jS F Y H:i:s') : 'N/A';

            $userSubscriptionData = [
                '_id' => $userSubscription->_id,
                '_uid' => $userSubscription->_uid,
                'created_at' => formatDate($userSubscription->created_at, 'l jS F Y H:i:s'),
                'users__id' => $userSubscription->users__id,
                'expiry_at' => $expiryAt,
                'credit_wallet_transactions__id' => $userSubscription->credit_wallet_transactions__id,
                'debitedCredits' => $userSubscription->credits,
                'plan_id' => $userSubscription->plan_id,
                'planTitle' => $planData['title'],
                'planPrice' => $planData['price'],
                'long_expiry' => false,
            ];
            // check if it has long expiry date
            if($userSubscription->expiry_at > Carbon::now()->addYears(10)) {
                $userSubscriptionData['is_long_expiry'] = true;
            }
        }

        return $this->engineReaction(1, [
            'premiumPlanData' => [
                'isPremiumUser' => isPremiumUser(),
                'userSubscriptionData' => $userSubscriptionData,
                'premiumPlans' => $premiumPlanData,
                'premiumFeature' => $premiumFeatureData,
            ],
        ]);
    }

    /**
     * Process buy user premium plan.
     *
     *-----------------------------------------------------------------------*/
    public function processBuyPremiumPlan($inputData)
    {
        //buy premium plan request
        $buyPremiumPlanRequest = $this->userRepository->processTransaction(function () use ($inputData) {
            $premiumPlanCollection = getStoreSettings('plan_duration');
            //if user already a premium user then throw error
            $isPremiumUser = isPremiumUser(null, true);
            // if (!__isEmpty($isPremiumUser)) {
                // return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('You are already Premium user'));
            // }

            // check if user premium plan exists
            if (! __isEmpty($inputData['select_plan'])) {
                $premiumPlans = is_array($premiumPlanCollection) ? $premiumPlanCollection : json_decode($premiumPlanCollection, true);
                $selectedPlan = $premiumPlans[$inputData['select_plan']];
                //check if plan exist
                if (! __isEmpty($selectedPlan)) {
                    //fetch user credits data
                    $totalUserCredits = totalUserCredits();

                    //if premium plan credit price greater then total user credits then show error message
                    if ($selectedPlan['price'] > $totalUserCredits) {
                        return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Your credit balance is too low, please purchase credits.'));
                    }

                    $expiryTime = null;
                    $messageForSuccess = __tr('You are now Premium User');
                    if (!__isEmpty($isPremiumUser)) {
                        $currentDateTime = Carbon::parse($isPremiumUser->expiry_at);
                        if($isPremiumUser->expiry_at > Carbon::now()->addYears(10)) {
                            return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('your premium user expiry is long enough'));
                        }
                        $messageForSuccess = __tr('Premium plan expiry has been extended');
                    } else {
                        $currentDateTime = Carbon::now();
                    }
                    // get expiry time on current selected plan
                    switch ($inputData['select_plan']) {
                        case 'one_day':
                            $expiryTime = $currentDateTime->addDay();
                            break;
                        case 'one_week':
                            $expiryTime = $currentDateTime->addWeek();
                            break;
                        case 'one_month':
                            $expiryTime = $currentDateTime->addMonth();
                            break;
                        case 'half_year':
                            $expiryTime = $currentDateTime->addMonths(6);
                            break;
                        case 'year':
                            $expiryTime = $currentDateTime->addYear();
                            break;
                        case 'life_time':
                            $expiryTime = $currentDateTime->addYears(100);
                            break;
                    }

                    //credit wallet store data
                    $creditWalletStoreData = [
                        'status' => 1,
                        'users__id' => getUserID(),
                        'credits' => '-'.''.$selectedPlan['price'],
                    ];

                    //store credit wallet data and user subscription data
                    if ($creditWalledId = $this->userRepository->storeCreditWalletTransaction($creditWalletStoreData)) {
                        //user subscription store data
                        $storeSubscriptionData = [
                            'status' => 1,
                            'users__id' => getUserID(),
                            'expiry_at' => $expiryTime,
                            'plan_id' => $inputData['select_plan'],
                            'credit_wallet_transactions__id' => $creditWalledId,
                        ];
                        //store user subscription data
                        if ($this->userRepository->storeUserSubscription($storeSubscriptionData)) {
                            return $this->userRepository->transactionResponse(1, ['show_message' => true], $messageForSuccess);
                        }
                    }
                    //error response
                    return $this->userRepository->transactionResponse(1, ['show_message' => true], __tr('Something went wrong, please contact to administrator.'));
                } else {
                    //error response
                    return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Selected plan not exists.'));
                }
            } else {
                //error response
                return $this->userRepository->transactionResponse(2, ['show_message' => true], __tr('Please select plan first.'));
            }
        });

        //response
        return $this->engineReaction($buyPremiumPlanRequest);
    }
}
