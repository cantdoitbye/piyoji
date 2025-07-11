<?php

namespace App\Repositories\Interfaces;

interface SellerRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveSellersList();
    
    public function getSellersByTeaGrade(string $grade);
    
    public function searchSellers(string $query);
    
    public function getSellerWithContracts(int $id);
    
    public function getSellerStatistics();
    
    public function updateStatus(int $id, bool $status);
    
    public function getSellersByStatus(bool $status);
    
    public function checkEmailExists(string $email, int $excludeId = null);
    
    public function checkGstinExists(string $gstin, int $excludeId = null);
    
    public function checkPanExists(string $pan, int $excludeId = null);
}