<?php

namespace App\Repositories\Interfaces;

interface LogisticCompanyRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveLogisticCompaniesList();
    
    public function getLogisticCompaniesByRegion(string $region);
    
    public function getLogisticCompaniesByRoute(string $route);
    
    public function getLogisticCompaniesByState(string $state);
    
    public function searchLogisticCompanies(string $query);
    
    public function getLogisticCompanyStatistics();
    
    public function updateStatus(int $id, bool $status);
    
    public function getLogisticCompaniesByStatus(bool $status);
    
    public function checkEmailExists(string $email, int $excludeId = null);
    
    public function checkGstinExists(string $gstin, int $excludeId = null);
    
    public function checkPanExists(string $pan, int $excludeId = null);
    
    public function getByPricingType(string $pricingType);
    
    public function getWithFilters(array $filters = []);
    
    public function getActiveCompaniesByRegionAndRoute(string $region = null, string $route = null);
}