<?php

namespace App\Yantrana\Base;

use App\Yantrana\__Laraware\Core\CoreController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use View;

abstract class BaseController extends CoreController
{
    /**
     * Load Public View
     *
     * @param  string  $viewName - View Name
     * @param  array  $data     - Array of data if needed
     * @return array
     *-------------------------------------------------------------------------- */
    public function loadManageView($viewName, $data = [], $options = [])
    {
        if(request()->ajax()) {
            return $this->responseAction(
                $this->processResponse(1, [], [], true),
                $this->replaceView($viewName, $data, '.lw-page-content')
            );
        }
        
        return $this->renderView('manage-master', $viewName, $data, $options);
    }

    /**
     * Load Public View
     *
     * @param  string  $viewName - View Name
     * @param  array  $data     - Array of data if needed
     * @return array
     *-------------------------------------------------------------------------- */
    public function loadPublicView($viewName, $data = [], $options = [])
    {
        $options = array_merge([
            'replaceElement' => '.lw-page-content',
            'responseData' => []
        ], $options);

        if(request()->ajax()) {
            return $this->responseAction(
                $this->processResponse(1, [], $options['responseData'], true),
                $this->replaceView($viewName, $data, $options['replaceElement'])
            );
        }

        return $this->renderView('public-master', $viewName, $data, $options);
    }

    /**
     * Load Public View
     *
     * @param  string  $viewName - View Name
     * @param  array  $data     - Array of data if needed
     * @return array
     *-------------------------------------------------------------------------- */
    protected function renderView($mainView, $viewName, $data = [], $options = [])
    {
        $options = array_merge([
            'compress_page' => true,
        ], $options);
        if(!$data) {
            $data = [];
        }
        try {
            $output = view($mainView, $data)->nest('pageRequested', $viewName, $data)->render();
        } catch (\Throwable $th) {
            //throw $th;
            abort(404);
        }

        if ((config('app.debug', false) === false)
        and $options['compress_page'] === true
    ) {
            $filters = [
                '/<!--([^\[|(<!)].*)/' => '',  // Remove HTML Comments (breaks with HTML5 Boilerplate)
                '/(?<!\S)\/\/\s*[^\r\n]*/' => '',  // Remove comments in the form /* */
                '/\s{2,}/' => ' ', // Shorten multiple white spaces
                '/(\r?\n)/' => '',  // Collapse new lines
            ];

            return preg_replace(
                array_keys($filters),
                array_values($filters),
                $output
            );
        } else { // for clog
            $clogSessItemName = '__clog';
            if (! empty(config('app.'.$clogSessItemName, []))) {
                $responseData = [
                    '__dd' => true,
                    '__clogType' => 'NonAjax',
                    $clogSessItemName => config('app.'.$clogSessItemName),
                ];

                //reset the __clog items in session
                config(['app.'.$clogSessItemName => []]);
                $output = $output.'<script type="text/javascript">__globals.clog('.json_encode($responseData).');</script>';
            }
        }

        return $output;
    }

    /**
     * Send response to client
     *
     *
     * @return array
     *-------------------------------------------------------------------------- */
    public function responseAction($processResponse, $typeResponse = [])
    {
        $originalData = $processResponse->getData();
        $originalData->response_action = array_merge([
            'type' => null, // redirect, replace, append, prepend
            'target' => null, // replacement element identifier or redirect url
            'content' => null,
            'url' => null,
        ], $typeResponse);

        $processResponse->setData($originalData);

        return $processResponse;
    }

    /**
     * Replace view preparation
     *
     *
     * @return array
     *-------------------------------------------------------------------------- */
    public function replaceView($viewName, $data = [], $targetElement = '#pageContent')
    {
        return [
            'type' => 'replace', // redirect, replace, append, prepend
            'target' => $targetElement, // replacement element identifier or redirect url
            'content' => view($viewName, $data)->render(),
        ];
    }

    /**
     * Replace view preparation
     *
     *
     * @return array
     *-------------------------------------------------------------------------- */
    public function redirectTo($routeOrUrl, $parameters = [])
    {
        return [
            'type' => 'redirect', // redirect, replace, append, prepend
            'url' => Str::startsWith($routeOrUrl, 'http') ? $routeOrUrl : route($routeOrUrl, $parameters),
        ];
    }

    /**
     * Prepare data for clideside.
     *
     * @return array
     */
    protected function prepareForBrowser()
    {
        // get all application routes.
        $routeCollection = Route::getRoutes();
        $routes = [];
        $availableRoutes = [];
        $index = 1;

        // if routes in application
        if (! empty($routeCollection)) {
            foreach ($routeCollection as $route) {
                if ($route->getName()) {
                    $routes[$route->getName()] = $route->uri();
                    $availableRoutes[] = $route->getName();
                } else {
                    $routes['unnamed_'.$index] = $route->uri();
                }

                $index++;
            }
        }

        return [
            '__appImmutables' => [
                'auth_info' => getUserAuthInfo(),
                'availableRoutes' => $availableRoutes,
                'routes' => $routes,
                'agora_app_id' => getStoreSettings('agora_app_id'),
                'pusher_key' => getStoreSettings('pusher_app_key'),
                'pusher_cluster_key' => getStoreSettings('pusher_app_cluster_key'),
                'messages' => [
                    'validation' => trans('validation'),
                    'js_string' => trans('js-string'),
                ],
            ],
            'appConfig' => [
                'debug' => config('app.debug', false),
                'appBaseURL' => asset(''),
            ],
        ];
    }
}
