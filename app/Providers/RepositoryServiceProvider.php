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
    CourierRepositoryInterface,
    LogisticCompanyRepositoryInterface,
    ContractRepositoryInterface,
    ContractItemRepositoryInterface,
    UserRepositoryInterface
};

// Repository Implementations
use App\Repositories\{
    BaseRepository,
    AdminUserRepository,
    SellerRepository,
    BuyerRepository,
    CourierRepository,
    LogisticCompanyRepository,
    ContractRepository,
    ContractItemRepository,
    UserRepository

};

// Service Interfaces
use App\Services\Interfaces\BaseServiceInterface;

// Service Implementations
use App\Services\{
    SellerService,
    BuyerService,
    CourierService,
    LogisticCompanyService,
    ContractService
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

         // Logistic Company Repository and Service
        $this->app->bind(LogisticCompanyRepositoryInterface::class, LogisticCompanyRepository::class);
        $this->app->bind(LogisticCompanyService::class, function ($app) {
            return new LogisticCompanyService($app->make(LogisticCompanyRepositoryInterface::class));
        });

          $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        
        // Contract Repository and Service
        $this->app->bind(ContractRepositoryInterface::class, ContractRepository::class);
        $this->app->bind(ContractItemRepositoryInterface::class, ContractItemRepository::class);
        $this->app->bind(ContractService::class, function ($app) {
            return new ContractService(
                $app->make(ContractRepositoryInterface::class),
                $app->make(ContractItemRepositoryInterface::class)
            );
        });
    }

    public function boot()
    {
        //
    }
}