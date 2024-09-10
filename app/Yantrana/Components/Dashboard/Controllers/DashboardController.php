<?php
/**
* DashboardController.php - Controller file
*
* This file is part of the Dashboard component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Dashboard\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\Dashboard\DashboardEngine;

class DashboardController extends BaseController
{
    /**
     * @var  DashboardEngine - Dashboard Engine
     */
    protected $dashboardEngine;

    /**
     * Constructor
     *
     * @param  DashboardEngine  $dashboardEngine - Dashboard Engine
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(DashboardEngine $dashboardEngine)
    {
        $this->dashboardEngine = $dashboardEngine;
    }

    /**
     * Show dashboard view.
     *---------------------------------------------------------------- */
    public function loadDashboardView()
    {
        $data = $this->dashboardEngine->prepareDashboard();

        return $this->loadManageView('dashboard.dashboard', $data);
    }
}
