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
    UserRepositoryInterface,
    SampleRepositoryInterface,
     PocRepositoryInterface,
    TeaRepositoryInterface,
    GardenRepositoryInterface,
    SampleBuyerAssignmentRepositoryInterface
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
    UserRepository,
    SampleRepository,
     PocRepository,
    TeaRepository,
    GardenRepository,
    SampleBuyerAssignmentRepository
};

// Service Interfaces
use App\Services\Interfaces\BaseServiceInterface;

// Service Implementations
use App\Services\{
    BuyerAssignmentService,
    SellerService,
    BuyerService,
    CourierService,
    LogisticCompanyService,
    ContractService,
    SampleService,
     PocService,
    TeaService,
    GardenService 
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
    return new SellerService(
        $app->make(SellerRepositoryInterface::class),
        $app->make(GardenRepositoryInterface::class)
    );
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

           $this->app->bind(SampleRepositoryInterface::class, SampleRepository::class);
        $this->app->bind(SampleService::class, function ($app) {
            return new SampleService(
                $app->make(SampleRepositoryInterface::class),
                $app->make(SellerRepositoryInterface::class)
            );
        });

         // POC Repository and Service
        $this->app->bind(PocRepositoryInterface::class, PocRepository::class);
        $this->app->bind(PocService::class, function ($app) {
            return new PocService($app->make(PocRepositoryInterface::class));
        });
        
        // Tea Repository and Service
        $this->app->bind(TeaRepositoryInterface::class, TeaRepository::class);
        $this->app->bind(TeaService::class, function ($app) {
            return new TeaService($app->make(TeaRepositoryInterface::class));
        });
        
        // Garden Repository and Service
        $this->app->bind(GardenRepositoryInterface::class, GardenRepository::class);
        $this->app->bind(GardenService::class, function ($app) {
            return new GardenService(
                $app->make(GardenRepositoryInterface::class),
                $app->make(TeaRepositoryInterface::class)
            );
        });

          $this->app->bind(
        SampleBuyerAssignmentRepositoryInterface::class,
        SampleBuyerAssignmentRepository::class
    );

         $this->app->singleton(BuyerAssignmentService::class, function ($app) {
        return new BuyerAssignmentService(
            $app->make(SampleRepositoryInterface::class),
            $app->make(SampleBuyerAssignmentRepositoryInterface::class),
            $app->make(BuyerRepositoryInterface::class)
        );
    });
    }

    public function boot()
    {
        //
    }
}