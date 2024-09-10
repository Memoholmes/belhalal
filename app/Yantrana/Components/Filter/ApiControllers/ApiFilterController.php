<?php
/**
* FilterController.php - Controller file
*
* This file is part of the Filter component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Filter\ApiControllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Filter\FilterEngine;
use App\Yantrana\Support\CommonUnsecuredPostRequest;

class ApiFilterController extends BaseController
{
    /**
     * @var  FilterEngine - Filter Engine
     */
    protected $filterEngine;

    /**
     * Constructor
     *
     * @param  FilterEngine  $filterEngine - Filter Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(FilterEngine $filterEngine)
    {
        $this->filterEngine = $filterEngine;
    }

    /**
     * Get Filter data and show filter view
     *
     * @param obj CommonUnsecuredPostRequest $request
     *
     * return view
     *-----------------------------------------------------------------------*/
    public function getFindMatchSupportData(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->filterEngine->prepareApiBasicFilterData($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Get Filter data and show filter view
     *
     * @param obj CommonUnsecuredPostRequest $request
     *
     * return view
     *-----------------------------------------------------------------------*/
    public function getFindMatches(CommonUnsecuredPostRequest $request)
    {
        $processReaction = $this->filterEngine->processFilterData($request->all());

        return $this->processResponse($processReaction, [], [], true);
    }
}
