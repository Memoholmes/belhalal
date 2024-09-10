<?php
/**
* ConfigurationController.php - Controller file
*
* This file is part of the Configuration component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Configuration\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Configuration\ConfigurationEngine;
use App\Yantrana\Components\Configuration\Requests\ConfigurationRequest;
use Artisan;

class ConfigurationController extends BaseController
{
    /**
     * @var  ConfigurationEngine - Configuration Engine
     */
    protected $configurationEngine;

    /**
     * Constructor
     *
     * @param  ConfigurationEngine  $configurationEngine - Configuration Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(ConfigurationEngine $configurationEngine)
    {
        $this->configurationEngine = $configurationEngine;
    }

    /**
     * Get Configuration Data.
     *
     * @param  string  $pageType
     * @return json object
     *---------------------------------------------------------------- */
    public function getConfiguration($pageType)
    {
        $processReaction = $this->configurationEngine->prepareConfigurations($pageType);

        return $this->loadManageView('configuration.settings', $processReaction['data']);
    }

    /**
     * Get Configuration Data.
     *
     * @param  string  $pageType
     * @return json object
     *---------------------------------------------------------------- */
    public function processStoreConfiguration(ConfigurationRequest $request, $pageType)
    {
        $processReaction = $this->configurationEngine->processConfigurationsStore($pageType, $request->all());

        return $this->responseAction($this->processResponse($processReaction, [], [], true));
    }

    /**
     * Process To Store Custom Fields
     *
     * @param  string  $pageType
     * @return json object
     *---------------------------------------------------------------- */
    public function processToStoreCustomFields(ConfigurationRequest $request, $pageType)
    {
        $processReaction = $this->configurationEngine->processStoreCustomFields($pageType, $request->all());

        return $this->processResponse($processReaction, [], ['show_message' => true], true);
    }

    /**
     * Process To Add Section Fields
     *
     * @param  string  $pageType
     * @return json object
     *---------------------------------------------------------------- */
    public function processAddSection(ConfigurationRequest $request)
    {
        $processReaction = $this->configurationEngine->processAddSectionFields($request->all());

        return $this->processResponse($processReaction, [], ['show_message' => true], true);
    }

    /**
     * Get Item Info.
     *
     * @param  string  $groupName
     * @return json object
     *---------------------------------------------------------------- */
    public function getItemInfo($groupName, $itemPos)
    {
        $processReaction = $this->configurationEngine->getItemsInfo($groupName, $itemPos);
        return $this->processResponse($processReaction, [], [], true);
    }
    /**
     * Delete Fields.
     *
     * @param  int  $groupName
     * @param  int  $itemPos
     * @param  string  $field
     * @return json object
     *---------------------------------------------------------------- */
    public function processToDeleteCustomFields($groupName, $itemPos, $field)
    {
        $processReaction = $this->configurationEngine->processToDeleteCustomFields($groupName, $itemPos, $field);
        return $this->processResponse($processReaction, [], ['show_message' => true], true);
    }

    /**
     * Clear system cache
     *
     * @param  ManageItemAddRequest  $request
     * @return void
     *---------------------------------------------------------------- */
    public function clearSystemCache(ConfigurationRequest $request)
    {
        $homeRoute = route('manage.dashboard');
        $cacheClearCommands = [
            'optimize:clear',
            /* 'route:clear',
            'config:clear',
            'cache:clear',
            'view:clear',
            'clear-compiled */
        ];

        foreach ($cacheClearCommands as $cmd) {
            Artisan::call(''.$cmd.'');
        }
        if ($request->has('redirectTo')) {
            header('Location: '.base64_decode($request->get('redirectTo')));
        } else {
            header('Location: '.$homeRoute);
        }

        exit();
    }

    /**
     * Register view
     *
     * @return void
     *---------------------------------------------------------------- */
    public function registerProductView()
    {
        return $this->loadManageView('configuration.licence-information');
    }

    /**
     * Process product registration
     *
     * @param  ConfigurationRequest  $request
     * @return void
     *---------------------------------------------------------------- */
    public function processProductRegistration(ConfigurationRequest $request)
    {
        $processReaction = $this->configurationEngine->processProductRegistration($request->all());

        return $this->responseAction($this->processResponse($processReaction, [], [], true));
    }

    /**
     * Process product registration
     *
     * @param  ConfigurationRequest  $request
     * @return void
     *---------------------------------------------------------------- */
    public function processProductRegistrationRemoval(ConfigurationRequest $request)
    {
        $processReaction = $this->configurationEngine->processProductRegistrationRemoval();

        return $this->responseAction($this->processResponse($processReaction, [], [], true));
    }

    /**
     * mobile app configurations
     *
     * @return view
     */
    public function mobileAppConfigurations()
    {
        return $this->loadManageView('configuration.mobile-app', $this->configurationEngine->mobileAppData(), [
            'compress_page' => false
        ]);
    }

    /**
     * Email Templates View
     *
     * @return  array
     */
    public function emailTemplateView()
    {
        return $this->loadManageView('help.email-templates');
    }
}
