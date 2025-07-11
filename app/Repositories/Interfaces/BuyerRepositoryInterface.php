<?php

namespace App\Repositories\Interfaces;

interface BuyerRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveBuyersList();
    
    public function getBuyersByType(string $type);
    
    public function searchBuyers(string $query);
    
    public function getBuyerWithFeedbacks(int $id);
    
    public function getBuyerStatistics();
    
    public function updateStatus(int $id, bool $status);
    
    public function getBuyersByStatus(bool $status);
    
    public function checkEmailExists(string $email, int $excludeId = null);
    
    public function getBuyersByTeaGrade(string $grade);
    
    public function getWithFilters(array $filters = []);
}