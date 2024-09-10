<?php
/**
* MediaController.php - Controller file
*
* This file is part of the Media component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Configuration\ConfigurationEngine;
use App\Yantrana\Components\Media\MediaEngine;
use Illuminate\Http\Request;

class MediaController extends BaseController
{
    /**
     * @var  MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * @var  ConfigurationEngine - Configuration Engine
     */
    protected $configurationEngine;

    /**
     * Constructor
     *
     * @param  MediaEngine  $mediaEngine - Media Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(
        MediaEngine $mediaEngine,
        ConfigurationEngine $configurationEngine
    ) {
        $this->mediaEngine = $mediaEngine;
        $this->configurationEngine = $configurationEngine;
    }

    /**
     * Upload Temp Media.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadTempMedia(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadTempMedia($request->all(), 'all');

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Upload Gift Temp Media.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadGiftTempMedia(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadTempMedia($request->all(), 'gift');

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Upload Sticker Temp Media.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadStickerTempMedia(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadTempMedia($request->all(), 'sticker');

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Upload Package Temp Media.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadPackageTempMedia(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadTempMedia($request->all(), 'package');

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Upload Temp Media.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadCreditPackageTempUpload(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadTempMedia($request->all(), 'package');

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Upload Logo.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadLogo(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadLogo($request->all(), 'logo');

        // Check if file uploaded successfully
        if ($processReaction['reaction_code'] == 1) {
            $this->configurationEngine->processConfigurationsStore('general', [
                'logo_name' => $processReaction['data']['fileName'],
            ]);
        }

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Upload Logo.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadSmallLogo(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadSmallLogo($request->all(), 'small_logo');

        // Check if file uploaded successfully
        if ($processReaction['reaction_code'] == 1) {
            $this->configurationEngine->processConfigurationsStore('general', [
                'small_logo_name' => $processReaction['data']['fileName'],
            ]);
        }

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Upload Logo.
     *
     * @param object Request $request
     * @return json object
     *---------------------------------------------------------------- */
    public function uploadFavicon(Request $request)
    {
        $processReaction = $this->mediaEngine
            ->processUploadFavicon($request->all(), 'favicon');

        // Check if file uploaded successfully
        if ($processReaction['reaction_code'] == 1) {
            $this->configurationEngine->processConfigurationsStore('general', [
                'favicon_name' => $processReaction['data']['fileName'],
            ]);
        }

        return $this->processResponse($processReaction, [], [], true);
    }
}
