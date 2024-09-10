<?php

/**
 * MessengerController.php - Controller file
 *
 * This file is part of the Messenger component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Messenger\Controllers;

use Illuminate\Support\Arr;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\User\UserEngine;
use App\Yantrana\Support\CommonUnsecuredPostRequest;
use App\Yantrana\Components\Messenger\MessengerEngine;
use App\Yantrana\Components\User\Models\UserAuthorityModel;
use App\Yantrana\Components\Messenger\Requests\MessageRequest;

class FakeUserMessengerControllers extends BaseController
{
    /**
     * @var  MessengerEngine - Messenger Engine
     */
    protected $messengerEngine;

    /**
     * @var  UserEngine - User Engine
     */
    protected $userEngine;

    /**
     * Constructor
     *
     * @param  MessengerEngine  $messengerEngine - Messenger Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(MessengerEngine $messengerEngine, UserEngine $userEngine)
    {
        $this->messengerEngine = $messengerEngine;
        $this->userEngine = $userEngine;
    }

    public function fakeUsersMessengerReadList()
    {
        $fakeUsersProfile = $this->messengerEngine->prepareConversationOfFakeUsers();

        return $this->loadManageView('messenger.manage.fake-user.list', $fakeUsersProfile['data']);
    }


    /**
     * Get User Conversation
     *
     * @param  number  $userId
     * @return  void
     *-----------------------------------------------------------------------*/
    public function getFakeUserConversation($userId, $fakeUserId = null)
    {
        $processReaction = $this->messengerEngine->prepareUserMessage($userId, $fakeUserId);

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true), 
            $this->replaceView('messenger.fake-user-control-panel', $processReaction['data'], '#lwUserConversationContainer')
        );
    }



    /**
     * Accept / Decline message request
     *
     * @param obj CommonUnsecuredPostRequest $request
     * @param  number  $userId
     * @return  void
     *-----------------------------------------------------------------------*/
    public function acceptDeclineFakeUserMessageRequest(CommonUnsecuredPostRequest $request, $userId, $optionalLoggedInUserId)
    {
        $processReaction = $this->messengerEngine->processAcceptDeclineMessageRequest($request->all(), $userId, $optionalLoggedInUserId);

        return $this->processResponse($processReaction, [], [], true);
    }


    public function prepareFakeUserConversationList($fakeUserId)
    {
        $processReaction = $this->messengerEngine->prepareConversationList(null, $fakeUserId);

        $userAuthority = UserAuthorityModel::where('users__id', $fakeUserId)->first();
        //update user authority data
        if (!__isEmpty($userAuthority)) {
            $userAuthority->touch();
        }

        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true),
            $this->replaceView('messenger.manage.fake-user.conversation', $processReaction['data'], '#lwFakeUserConversationContainer')
        );
    }

    /**
     * Send Message
     *
     * @param  number  $userId
     * @return  void
     *-----------------------------------------------------------------------*/
    public function fakeUserSendMessage(MessageRequest $request, $userId)
    {
        $processReaction = $this->messengerEngine->processSendMessage($request->all(), $userId, $request->get('optionalLoggedInUserId'));

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Handle user like dislike request.
     *
     * @param object UserResetPasswordRequest $request
     * @param  string  $reminderToken
     * @return json object
     *---------------------------------------------------------------- */
    public function userLikeDislike($optionalLoggedInUserId, $toUserUid, $like)
    {
        $processReaction = $this->userEngine->processUserLikeDislike($toUserUid, $like, $optionalLoggedInUserId);

        //check reaction code equal to 1
        return $this->responseAction(
            $this->processResponse($processReaction, [], [], true)
        );
    }

    /**
     * Delete Single Message
     *
     * @param obj CommonUnsecuredPostRequest $request
     * @param  number  $chatId
     * @return  void
     *-----------------------------------------------------------------------*/
    public function deleteMessage(CommonUnsecuredPostRequest $request, $chatId, $userId, $optionalLoggedInUserId)
    {
        $processReaction = $this->messengerEngine->processDeleteMessage($chatId);

        return $this->getFakeUserConversation($userId, $optionalLoggedInUserId);
    }

    /**
     * Delete All Message
     *
     * @param obj CommonUnsecuredPostRequest $request
     * @param   int                      $userId
     * @param   int                      $optionalLoggedInUserId
     * @return  void
     *-----------------------------------------------------------------------*/
    public function deleteAllMessages(CommonUnsecuredPostRequest $request, $userId, $optionalLoggedInUserId)
    {
        $processReaction = $this->messengerEngine->processDeleteAllMessages($request->all());

        return $this->getFakeUserConversation($userId, $optionalLoggedInUserId);
    }
}
