<?php

/**
 * SocialAccessController.php - Controller file
 *
 * This file is part of the User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\User\Requests\SocialAccessRequest;
use App\Yantrana\Components\User\SocialAccessEngine;
use Socialite;

class SocialAccessController extends BaseController
{
    /**
     * @var SocialAccessEngine - SocialAccess Engine
     */
    protected $socialAccessEngine;

    /**
     * Constructor
     *
     * @param  SocialAccessEngine  $socialAccessEngine - SocialAccess Engine
     * @return void
     *-----------------------------------------------------------------------*/
    public function __construct(SocialAccessEngine $socialAccessEngine)
    {
        $this->socialAccessEngine = $socialAccessEngine;
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     *
     *---------------------------------------------------------------- */
    public function redirectToProvider($provider)
    {
        // match key & the provider name like google, facebook
        $providerName = getSocialProviderName($provider);

        //if provider name false throw error
        if ($providerName === false) {
            abort(404);
        }

        try {
            return Socialite::driver($providerName)->redirect();
        } catch (\Exception $e) {
            return redirect()->route('user.login')->with([
                'errorStatus' => true,
                'message' => __tr('Something went wrong, Please contact with administrator.'),
            ]);
        }
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return Response
     */
    public function handleProviderCallback($provider, SocialAccessRequest $request)
    {
        //get provider name
        $providerName = getSocialProviderName($provider);

        //if provider  name not exist then throw error
        if ($providerName === false) {
            abort(404);
        }

        $denyRequest = $request->input('error');
        $errorCode = $request->input('error_code');

        // Check app not found and user cancel dialog
        if ((int) $errorCode === 4201) { // User cancel dialog
            //if error then go to login page
            return redirect()->route('user.login')->with([
                'errorStatus' => true,
                'message' => __tr('App not found please contact administrator'),
            ]);
        }

        // check the request is deny then redirect user on login page
        if (
            __ifIsset($denyRequest)
            and $denyRequest === 'access_denied'
        ) {
            return redirect()->route('user.login')->with([
                'errorStatus' => true,
                'message' => __tr('You have denied access to from __provider__', [
                    '__provider__' => $providerName,
                ]),
            ]);
        }

        //process social user authentication
        $processReaction = $this->socialAccessEngine->processSocialAccess($providerName);

        //check reaction code equal to 1
        if ($processReaction['reaction_code'] === 1) {
            return redirect()->route('user.profile_view', ['username' => getUserAuthInfo('profile.username')])->with([
                'success' => true,
                'message' => __tr('Welcome, you are logged in successfully'),
            ]);

        //else go to login page
        } else {
            return redirect()->route('user.login')->with([
                'errorStatus' => true,
                'message' => isset($processReaction['message'])
                    ? $processReaction['message']
                    : __tr('Authentication failed. Please check your  email/password & try again.'),
            ]);
        }
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return Response
     */
    public function handleApiProviderCallback($provider, SocialAccessRequest $request)
    {
        //get provider name
        $providerName = getSocialProviderName($provider);

        //if provider  name not exist then throw error
        if ($providerName === false) {
            abort(404);
        }

        $denyRequest = $request->input('error');
        $errorCode = $request->input('error_code');

        // Check app not found and user cancel dialog
        if ((int) $errorCode === 4201) { // User cancel dialog
            //if error then go to login page
            return redirect()->route('user.login')->with([
                'errorStatus' => true,
                'message' => __tr('App not found please contact administrator'),
            ]);
        }

        // check the request is deny then redirect user on login page
        if (
            __ifIsset($denyRequest)
            and $denyRequest === 'access_denied'
        ) {
            return redirect()->route('user.login')->with([
                'errorStatus' => true,
                'message' => __tr('You have denied access to from __provider__', [
                    '__provider__' => $providerName,
                ]),
            ]);
        }

        //process social user authentication
        $processReaction = $this->socialAccessEngine->processSocialAccess($providerName);
        return $this->processResponse($processReaction, [], [], true);
    }
}
