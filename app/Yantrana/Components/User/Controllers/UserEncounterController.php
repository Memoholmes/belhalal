<?php
/**
* UserEncounterController.php - Controller file
*
* This file is part of the UserEncounter User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\User\UserEncounterEngine;
// form Requests
use App\Yantrana\Components\User\UserEngine;

class UserEncounterController extends BaseController
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
        $processReaction = $this->userEngine->processUserLikeDislike($toUserUid, $like);

        return $this->refreshEncounter();

        //check reaction code equal to 1
        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
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

        return $this->refreshEncounter();

        //check reaction code equal to 1
        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Refresh Encounter View
     *
     * @return void
     */
    private function refreshEncounter() {
        // get encounter data
        $encounterData = $this->userEncounterEngine->getEncounterUserData();
        return $this->loadPublicView('user.partial-templates.encounter-block', $encounterData['data'], [
            'replaceElement' => '#encounterUserBlock'
        ]);
    }
}
