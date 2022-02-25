<?php

namespace App\Providers;

use App\Repository\HostReservationRepository;
use App\Repository\Interfaces\HostReservationRepositoryInterface;
use App\Repository\Interfaces\OfficeImageRepositoryInterface;
use App\Repository\Interfaces\OfficeRepositoryInterface;
use App\Repository\Interfaces\UserReservationRepositoryInterface;
use App\Repository\OfficeImageRepository;
use App\Repository\OfficeRepository;
use App\Repository\UserReservationRepository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
       $this->app->bind(OfficeRepositoryInterface::class, OfficeRepository::class);
       $this->app->bind(OfficeImageRepositoryInterface::class, OfficeImageRepository::class);
       $this->app->bind(UserReservationRepositoryInterface::class, UserReservationRepository::class);
       $this->app->bind(HostReservationRepositoryInterface::class, HostReservationRepository::class);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
