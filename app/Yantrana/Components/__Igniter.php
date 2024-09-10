<?php

namespace App\Yantrana\Components;

use App\Yantrana\Base\BaseController;

class __Igniter extends BaseController
{
    /**
     * Get ac master view template
     *
     * @return void
     *---------------------------------------------------------------- */
    public function baseData()
    {
        return $this->processResponse(1, [], array_merge($this->prepareForBrowser(), [
            'auth_info' => getUserAuthInfo(),
        ]));
    }
}
