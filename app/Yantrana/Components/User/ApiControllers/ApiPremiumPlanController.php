<?php
/**
* ApiPremiumPlanController.php - Controller file
*
* This file is part of the Premium Plan User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\ApiControllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\User\PremiumPlanEngine;
use App\Yantrana\Support\CommonUnsecuredPostRequest;

// form Requests

class ApiPremiumPlanController extends BaseController
{
    /**
     * @var  PremiumPlanEngine - PremiumPlan Engine
     */
    protected $premiumPlanEngine;

    /**
     * Constructor
     *
     * @param  PremiumPlanEngine  $premiumPlanEngine - PremiumPlan Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(PremiumPlanEngine $premiumPlanEngine)
    {
        $this->premiumPlanEngine = $premiumPlanEngine;
    }

    /**
     * User Premium Plan View.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getPremiumPlanData()
    {
        $processReaction = $this->premiumPlanEngine->preparePremiumPlanUserData();

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle buy premium plan by user request.
     *
     * @param object CommonUnsecuredPostRequest $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function buyPremiumPlans(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->premiumPlanEngine->processBuyPremiumPlan($request['selectedPlan']);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * User Premium Plan Success View.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getPremiumPlanSuccessView()
    {
        return $this->loadPublicView('user.premium-plan.premium-plan-success');
    }
}
