@section('page-title', __tr("Dashboard"))
@section('head-title', __tr("Dashboard"))
@section('keywordName', strip_tags(__tr("Dashboard")))
@section('keyword', strip_tags(__tr("Dashboard")))
@section('description', strip_tags(__tr("Dashboard")))
@section('keywordDescription', strip_tags(__tr("Dashboard")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())


<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-200">
        <?= __tr("Dashboard") ?>
    </h1>
</div>

<!-- Counts -->
<div class="row">
    <!-- Users Online -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            <?= __tr("Users Online") ?>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= __tr('__onlineUsersCount__', [
                                                                                '__onlineUsersCount__' => $dashboardData['online']
                                                                            ]) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / Users Online -->

    <!-- Active Users -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <a class="nav-link p-0" href="<?= route('manage.user.view_list', ['status' => 1]) ?>">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= __tr("Active Users") ?>
                            </div>
                        </a>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= __tr('__activeUsersCount__', [
                                                                                '__activeUsersCount__' => $dashboardData['active']
                                                                            ]) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / Active Users -->

    <!-- Inactive Users -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <a class="nav-link p-0" href="<?= route('manage.user.view_list', ['status' => 2]) ?>">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?= __tr("Inactive Users") ?>
                            </div>
                        </a>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= __tr('__inactiveUsersCount__', [
                               '__inactiveUsersCount__' => $dashboardData['inactive']
                                   ]) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-times fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / Inactive Users -->

    <!-- Pending Requests Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <a class="nav-link p-0" href="<?= route('manage.user.view_list', ['status' => 3]) ?>">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                <?= __tr("Blocked Users") ?>
                            </div>
                        </a>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= __tr('__blockedUsersCount__', [
                                                                                '__blockedUsersCount__' => $dashboardData['blocked']
                                                                            ]) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Card Example -->
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <a class="nav-link p-0" href="<?= route('manage.abuse_report.read.list', ['status' => 1]) ?>">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1" > 
                            <?= __tr("Awaiting Abuse Reports") ?>
                        </div>
                    </a>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= __tr('__awaitingAbuseReportsCount__', [
                         '__awaitingAbuseReportsCount__' => $dashboardData['awaiting_abuse_report_count']
                 ]) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earning this month -->
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            <?= __tr("Current Month Earnings") ?>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= __tr('__currencyCode__ __currentMonthIncome__', [
                                '__currencyCode__' => $dashboardData['currency'],
                                '__currentMonthIncome__' => $dashboardData['current_month_income']
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / Earning this month -->

</div>

<div class="row">
    <div class="col-xl-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?= __tr("Earnings Overview") ?>
                </h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <div class="chartjs-size-monitor">
                        <div class="chartjs-size-monitor-expand">
                            <div class=""></div>
                        </div>
                        <div class="chartjs-size-monitor-shrink">
                            <div class=""></div>
                        </div>
                    </div>
                    <canvas id="myAreaChart" style="display: block; width: 668px; height: 320px;" width="668"
                        height="320" class="chartjs-render-monitor"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?= __tr('Last 12 Month Registrations') ?>
                </h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <div class="chartjs-size-monitor">
                        <div class="chartjs-size-monitor-expand">
                            <div class=""></div>
                        </div>
                        <div class="chartjs-size-monitor-shrink">
                            <div class=""></div>
                        </div>
                    </div>
                    <canvas id="myAreaChart2" style="display: block; width: 668px; height: 320px;" width="668"
                        height="320" class="chartjs-render-monitor"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


@lwPush('appScripts')
<script type="text/javascript">
    var year = @json($dashboardData['labels']),
    currency = '<?= $dashboardData['currency'] ?>';
    // Area Chart Example
    var ctx = document.getElementById("myAreaChart");
    var myLineChart = new Chart(ctx, {
        // locale:'mr'
        type: 'line',
        data: {
            labels: year,
            datasets: [{
                label: "{{ __tr('Earnings') }}",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "#c61d61",
                pointRadius: 3,
                pointBackgroundColor: "rgba(255,255,255, 1)",
                pointBorderColor: "rgba(255,255,255, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(255,255,255, 1)",
                pointHoverBorderColor: "rgba(255,255,255, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: <?= json_encode($dashboardData['month_wise_income']) ?>,
            }],
        },
        options: {
            locale : window.appConfig.locale,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            // scales: {
            //     x: [{
            //         time: {
            //             unit: 'date'
            //         },
            //         gridLines: {
            //             display: false,
            //             drawBorder: false
            //         },
            //         ticks: {
            //             maxTicksLimit: 7
            //         }
            //     }],
            //     yAxes: [{
            //         ticks: {
            //             maxTicksLimit: 5,
            //             padding: 10,
            //             // Include a dollar sign in the ticks
            //             callback: function(value, index, values) {
            //                 return (currency + value);
            //             }
            //         },
            //         gridLines: {
            //             color: "rgb(234, 236, 244)",
            //             zeroLineColor: "rgb(234, 236, 244)",
            //             drawBorder: false,
            //             borderDash: [2],
            //             zeroLineBorderDash: [2]
            //         }
            //     }],
            // },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#000000",
                titleMarginBottom: 10,
                titleFontColor: 'rgba(210, 0, 156, 1)',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': ' + currency + tooltipItem.yLabel;
                    }
                }
            }
        }
    });

    // Area Chart Example
    var ctx2 = document.getElementById("myAreaChart2");
    var myLineChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: year,
            datasets: Object.values(<?= json_encode($dashboardData['current_year_registrations']) ?>),
        },
        options: {
            locale : window.appConfig.locale,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            // scales: {
            //     xAxes: [{
            //         time: {
            //             unit: 'date'
            //         },
            //         gridLines: {
            //             display: false,
            //             drawBorder: false
            //         },
            //         ticks: {
            //             maxTicksLimit: 7
            //         }
            //     }],
            //     yAxes: [{
            //         ticks: {
            //             maxTicksLimit: 5,
            //             padding: 10,
            //             // Include a dollar sign in the ticks
            //             callback: function(value, index, values) {
            //                 return value;
            //             }
            //         },
            //         gridLines: {
            //             color: "rgb(234, 236, 244)",
            //             zeroLineColor: "rgb(234, 236, 244)",
            //             drawBorder: false,
            //             borderDash: [2],
            //             zeroLineBorderDash: [2]
            //         }
            //     }],
            // },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#000000",
                titleMarginBottom: 10,
                titleFontColor: 'rgba(210, 0, 156, 1)',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': ' + tooltipItem.yLabel;
                    }
                }
            }
        }
    });
</script>
@lwPushEnd