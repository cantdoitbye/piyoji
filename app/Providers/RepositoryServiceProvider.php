<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use App\Repositories\Interfaces\AdminUserRepositoryInterface;
// use App\Repositories\AdminUserRepository;
// Repository Interfaces
use App\Repositories\Interfaces\{
    BaseRepositoryInterface,
    AdminUserRepositoryInterface,
    SellerRepositoryInterface,
    BuyerRepositoryInterface,
    CourierRepositoryInterface
};

// Repository Implementations
use App\Repositories\{
    BaseRepository,
    AdminUserRepository,
    SellerRepository,
    BuyerRepository,
    CourierRepository
};

// Service Interfaces
use App\Services\Interfaces\BaseServiceInterface;

// Service Implementations
use App\Services\{
    SellerService,
    BuyerService,
    CourierService
};


class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AdminUserRepositoryInterface::class, AdminUserRepository::class);
     
         // Base Repository
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        
        // Seller Repository and Service
        $this->app->bind(SellerRepositoryInterface::class, SellerRepository::class);
        $this->app->bind(SellerService::class, function ($app) {
            return new SellerService($app->make(SellerRepositoryInterface::class));
        });
        
        // Buyer Repository and Service
        $this->app->bind(BuyerRepositoryInterface::class, BuyerRepository::class);
        $this->app->bind(BuyerService::class, function ($app) {
            return new BuyerService($app->make(BuyerRepositoryInterface::class));
        });
        
        // Courier Repository and Service
        $this->app->bind(CourierRepositoryInterface::class, CourierRepository::class);
        $this->app->bind(CourierService::class, function ($app) {
            return new CourierService($app->make(CourierRepositoryInterface::class));
        });
    }

    public function boot()
    {
        //
    }
}