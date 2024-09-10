<?php

namespace App\Http\Middleware;

use App\Yantrana\Components\User\Models\User;
use App\Yantrana\Components\User\Models\UserAuthorityModel;
use Auth;
use Closure;
use Illuminate\Support\Facades\Route;
use YesTokenAuth;

class ApiAuthenticateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config([
            '__tech.auth_info' => [
                'authorized' => false,
                'reaction_code' => 9,
            ],
        ]);

        $isVerified = YesTokenAuth::verifyToken();

        if(($isVerified['error'] or ($isVerified['error'] !== false)) and (Route::currentRouteName() != 'base_data')) {
            return __apiResponse([
                'message' => $isVerified['error'],
                'auth_info' => getUserAuthInfo(),
            ], 2);
        }

        $tokenRefreshed = array_get($isVerified, 'refreshed_token', null);

        if ($tokenRefreshed) {
            /*set refreshed token */
            setAccessToken($tokenRefreshed);
        }
        if (!$isVerified['error'] or ($isVerified['error'] === false)) {
            $userInfo = User::where('_id', $isVerified['aud'])->first();
            if (! __isEmpty($userInfo) && in_array($userInfo->status, [2, 3, 4, 5, 6])) {
                return __apiResponse([
                    'message' => __tr('Your Account seems to Inactive or Deleted or Not Activated, Please contact Administrator.'),
                    'auth_info' => getUserAuthInfo(),
                ], 9);
            } elseif (! __isEmpty($userInfo) && $userInfo->status == 1) {
                Auth::loginUsingId($isVerified['aud']);
                //find user authority data
                $userAuthority = UserAuthorityModel::where('users__id', $userInfo->_id)->first();
                $currentRouteName = Route::currentRouteName();
                //update user authority data
                if (! __isEmpty($userAuthority) and $currentRouteName != 'user.logout') {
                    $userAuthority->touch();
                }
            } else {
                if (Route::currentRouteName() != 'base_data') {
                    return __apiResponse([
                        'message' => __tr('Please login to complete request.'),
                        'auth_info' => getUserAuthInfo(),
                    ], 9);
                }
            }
        }

        updateClientModels([
            'notifications' => [
                'notificationCount' => getNotificationCount(),
            ]
        ]);

        return $next($request);
    }
}
