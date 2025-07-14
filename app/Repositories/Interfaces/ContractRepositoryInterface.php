<?php

namespace App\Repositories\Interfaces;

interface ContractRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveContracts();
    
    public function getExpiredContracts();
    
    public function getExpiringSoonContracts(int $days = 30);
    
    public function getContractsBySeller(int $sellerId);
    
    public function getActiveContractsBySeller(int $sellerId);
    
    public function getLatestContractBySeller(int $sellerId);
    
    public function getContractsByTeaGrade(string $teaGrade);
    
    public function searchContracts(string $query);
    
    public function getContractStatistics();
    
    public function getContractWithItems(int $id);
    
    public function getContractsWithExpiryAlerts();
    
    public function updateContractStatus(int $id, string $status);
    
    public function expireOldContracts();
    
    public function getWithFilters(array $filters = []);
    
    public function getPriceForSellerAndTeaGrade(int $sellerId, string $teaGrade);
    
    public function getAvailableTeaGradesForSeller(int $sellerId);
}