<?php
/**
* UserEncounterController.php - Controller file
*
* This file is part of the UserEncounter User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\ApiControllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\User\UserEncounterEngine;
// form Requests
use App\Yantrana\Components\User\UserEngine;

class ApiUserEncounterController extends BaseController
{
    /**
     * @var  UserEncounterEngine - UserEncounter Engine
     */
    protected $userEncounterEngine;

    /**
     * @var UserEngine - User Engine
     */
    protected $userEngine;

    /**
     * Constructor
     *
     * @param  UserEncounterEngine  $userEncounterEngine - UserEncounter Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(
        UserEncounterEngine $userEncounterEngine,
        UserEngine $userEngine
    ) {
        $this->userEncounterEngine = $userEncounterEngine;
        $this->userEngine = $userEngine;
    }

    /**
     * Handle user like dislike request.
     *
     * @param  string  $toUserUid, $like
     * @return json object
     *---------------------------------------------------------------- */
    public function userEncounterLikeDislike($toUserUid, $like)
    {
        //if like type is dislike value is 2 then update value to 0
        //only for dislike option
        if ($like == 2) {
            $like = 0;
        }

        $processReaction = $this->userEngine->processUserLikeDislike($toUserUid, $like);

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle skip encounter user request.
     *
     * @param  string  $toUserUid, $like
     * @return json object
     *---------------------------------------------------------------- */
    public function skipEncounterUser($toUserUid)
    {
        $processReaction = $this->userEncounterEngine->processSkipEncounterUser($toUserUid);

        return $this->processResponse($processReaction, [], [], true);
    }
}
