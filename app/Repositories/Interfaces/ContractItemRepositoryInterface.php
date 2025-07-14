<?php 

namespace App\Repositories\Interfaces;

interface ContractItemRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveItemsByContract(int $contractId);
    
    public function getItemsByTeaGrade(string $teaGrade);
    
    public function getItemsByContractAndTeaGrade(int $contractId, string $teaGrade);
    
    public function updateItemStatus(int $id, bool $status);
    
    public function bulkCreateItems(int $contractId, array $items);
    
    public function deleteItemsByContract(int $contractId);
    
    public function getTeaGradesByContract(int $contractId);
}