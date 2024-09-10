<?php

namespace App\Providers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //for collection method
        Collection::macro('paginate', function (int $perPage = 15, $page = null, $options = []) {
            $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage)->values(),
                $this->count(),
                $perPage,
                $page,
                $options
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Helpers & other additional resources from Angulara & __Laraware
        require app_path('Yantrana/__Laraware/Support/helpers.php');
        require app_path('Yantrana/Support/app-helpers.php');
        require app_path('Yantrana/Support/extended-validations.php');

        // config items requires gettext helper function to work
        // require app_path('Yantrana/Support/custom-tech-config.php');
        require app_path('Yantrana/Support/extended-blade-directive.php');
    }
}
