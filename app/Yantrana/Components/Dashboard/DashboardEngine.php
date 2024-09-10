<?php

/**
 * DashboardEngine.php - Main component file
 *
 * This file is part of the Dashboard component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Dashboard;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Dashboard\Interfaces\DashboardEngineInterface;
use App\Yantrana\Components\Dashboard\Repositories\DashboardRepository;

class DashboardEngine extends BaseEngine implements DashboardEngineInterface
{
    /**
     * @var  DashboardRepository - Dashboard Repository
     */
    protected $dashboardRepository;

    /**
     * Constructor
     *
     * @param  DashboardRepository  $dashboardRepository - Dashboard Repository
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Prepare Dashboard
     *
     * @return  void
     *-----------------------------------------------------------------------*/
    public function prepareDashboard()
    {
        //all users
        $users = $this->dashboardRepository->fetchUsers();
        //online users
        $onlineUsers = $this->dashboardRepository->fetchOnlineUsers();
        //abuse reports awaiting moderation
        $abuseReportCount = $this->dashboardRepository->abuseReports(1)->count();
        // all transactions
        $transactions = $this->dashboardRepository->currentYearFinancialTransactions(2)->toArray();
        //monthly transactions
        $monthlyTransactions = collect($transactions)->groupBy('month');

        // all users registered in current year
        $currentYearRegistrations = $this->dashboardRepository->currentYearRegistrations()->toArray();
        //month wise collection
        $monthWiseUsers = collect($currentYearRegistrations)->groupBy(['gender', 'month']);

        $datasets = [
            1 => [
                'label' => 'Male',
                'backgroundColor' => 'blue',
                'data' => array_fill(0, 12, 0),
            ],
            2 => [
                'label' => 'Female',
                'backgroundColor' => 'pink',
                'borderColor' => 'pink',
                'borderWidth' => 1,
                'data' => array_fill(0, 12, 0),
            ],
            3 => [
                'label' => 'Secret',
                'backgroundColor' => 'grey',
                'borderColor' => 'grey',
                'borderWidth' => 1,
                'data' => array_fill(0, 12, 0),
            ],
        ];
        $allTheMonths = collect(range(11, 0))->map(function ($i) {
            $dt = today()->startOfMonth()->subMonth($i);
            return $dt->month;
        });


        //counts
        $dashboardCounts = [
            'online' => $onlineUsers->count(),
            'active' => 0,
            'inactive' => 0,
            'blocked' => 0,
            'awaiting_abuse_report_count' => $abuseReportCount,
            'current_month_income' => 0,
            'month_wise_income' => array_fill(0, 12, 0),
            'currency' => getCurrencySymbol(),
            'current_year_registrations' => $datasets,
            'labels' => [],
        ];
        $monthWiseUsersArr = [];

        for ($i = 0; $i < count($allTheMonths); $i++) {
            for ($j = 0; $j < count($datasets); $j++) {
                $monthWiseUsersArr[$j][$allTheMonths[$i]] = 0;
            }
        }

        if (!__isEmpty($monthWiseUsers)) {
            foreach ($monthWiseUsers as $key => $monthWiseUser) {
                foreach ($monthWiseUser as $key2 => $mtUsers) {
                    $monthWiseUsersArr[$key - 1][$key2] = $mtUsers->count();
                    // $dashboardCounts['current_year_registrations'][$key]['data'][$key2 - 1] = $mtUsers->count();
                }
            }
        }


        foreach ($monthWiseUsersArr as $monthWiseUsersArrKey => $monthWiseUsersArrValue) {
            $j = 0;
            foreach ($monthWiseUsersArrValue as $key => $value) {
                $dashboardCounts['current_year_registrations'][$monthWiseUsersArrKey + 1]['data'][$j] = $value;
                $j++;
            }
        }


        $allTheMonths = collect(range(11, 0))->map(function ($i) {
            $dt = today()->startOfMonth()->subMonth($i);
            return [
                'month_number' => $dt->month,
                'month_name' => $dt->translatedFormat('M') . ' ' . $dt->translatedFormat('y'),
            ];
        });

        $months = $last12MonthsLabels = [];
        for ($i = 0; $i < count($allTheMonths); $i++) {
            $dashboardCounts['labels'][$i] = $allTheMonths[$i]['month_name'];
            $months[$allTheMonths[$i]['month_number']] = 0;
        }

        $currentMonth = (int) date('m');

        if (!__isEmpty($monthlyTransactions)) {
            foreach ($monthlyTransactions as $key => $trans) {
                if (!__isEmpty($trans)) {
                    $amount = array_sum($trans->pluck('amount')->toArray());
                    //month wise income
                    $months[$key] = $amount;
                    // $dashboardCounts['month_wise_income'][$months[$key]] = $amount;
                    //current month earning
                    if ($currentMonth == $key) {
                        $dashboardCounts['current_month_income'] = number_format((float) $amount, 2);
                    }
                }
            }
        }

        $i = 0;
        foreach ($months as $monthKey => $month) {
            $dashboardCounts['month_wise_income'][$i] = $months[$monthKey];
            $i++;
        }

        //check if users not empty
        if (!__isEmpty($users)) {
            foreach ($users->groupBy('status') as $key => $status) {
                switch ($key) {
                    case 1:
                        $dashboardCounts['active'] = $status->count();
                        break;
                    case 2:
                        $dashboardCounts['inactive'] = $status->count();
                        break;
                    case 3:
                        $dashboardCounts['blocked'] = $status->count();
                        break;
                    default:
                        break;
                }
            }
        }
        return [
            'dashboardData' => $dashboardCounts,
        ];
    }
}
