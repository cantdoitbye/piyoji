<?php

namespace App\Repositories\Interfaces;

interface CourierRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveCouriersList();
    
    public function getCouriersByServiceArea(string $area);
    
    public function searchCouriers(string $query);
    
    public function getCourierWithShipments(int $id);
    
    public function getCourierStatistics();
    
    public function updateStatus(int $id, bool $status);
    
    public function getCouriersByStatus(bool $status);
    
    public function checkEmailExists(string $email, int $excludeId = null);
    
    public function getWithFilters(array $filters = []);
    
    public function getCouriersWithApiIntegration();
    
    public function testApiConnection(int $id);

}