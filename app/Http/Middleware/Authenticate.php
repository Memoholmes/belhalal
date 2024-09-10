<?php

namespace App\Http\Middleware;

use App\Yantrana\Components\User\Models\UserAuthorityModel;
use Auth;
use Closure;
use Illuminate\Support\Facades\Route;
use Session;
use URL;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // check if user is exists
        if (__isEmpty($user) or $user->status != 1) {
            if ($request->ajax()) {
                return __apiResponse([
                    'message' => __tr('Your account does not seems to be active, please contact administrator.'),
                    'auth_info' => getUserAuthInfo(11),
                ], 11);
            }

            // Check if user is logged in then logout that user
            if (Auth::check()) {
                Auth::logout();
            }

            Session::put('intendedUrl', URL::current());

            return redirect()->route('user.login')
                ->with([
                    'error' => true,
                    'message' => __tr('Your account does not seems to be active, please contact administrator.'),
                ]);
        }

        $includeRoutes = [
            'user.change_password.process',
            'user.change_email.process',
        ];

        $currentRouteName = $request->route()->getName();
        // check if demo mode is on
        if (
            $request->isMethod('post')
            and isDemo()
            and (in_array($currentRouteName, $includeRoutes))
            and isAdmin() and (getUserID() != 1)
        ) {
            return __apiResponse([
                'message' => __tr('Saving functionality is disabled in this demo.'),
                'show_message' => true,
            ], 1);
        }

        // check if user exists
        if (isset($user) and ! __isEmpty($user)) {
            //find user authority data
            $userAuthority = UserAuthorityModel::where('users__id', $user->_id)->first();
            $currentRouteName = Route::currentRouteName();
            //update user authority data
            if (! __isEmpty($userAuthority) and $currentRouteName != 'user.logout') {
                $userAuthority->touch();
            }

            if ($userAuthority->user_roles__id != 1) {
                // check if user profile is completed or not
                if (! isProfileComplete($user->_id)) {
                    if (! in_array($currentRouteName, [
                        'user.update_profile.wizard',
                        'user.upload_cover_image',
                        'user.upload_profile_image',
                        'user.write.location_data',
                        'user.profile.wizard_completed',
                        'user.write.update_profile_wizard',
                        'user.logout',
                        'user.get_profile_data',
                        'user.read.search_static_cities',
                        'user.write.store_city',
                    ])) {
                        if ($request->ajax()) {
                            return __apiResponse([
                                'message' => __tr('Please fill your profile information.'),
                                'redirectUrl' => route('user.update_profile.wizard'),
                            ], 21);
                        } else {
                            return redirect()->route('user.update_profile.wizard');
                        }
                    }
                } else {
                    if ($currentRouteName == 'user.update_profile.wizard') {
                        return redirect()->route('user.profile_view', [
                            'username' => getUserAuthInfo('profile.username'),
                        ]);
                    }
                }
            } else {
                if ($currentRouteName == 'user.update_profile.wizard') {
                    return redirect()->route('user.profile_view', [
                        'username' => getUserAuthInfo('profile.username'),
                    ]);
                }
            }
        }

        return $next($request);
    }
}
