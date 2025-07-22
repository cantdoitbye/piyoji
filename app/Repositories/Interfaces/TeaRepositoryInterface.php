<?php

namespace App\Repositories\Interfaces;

interface TeaRepositoryInterface extends BaseRepositoryInterface
{
    public function getModel();
    public function getActiveTeasList();
    public function getTeasByCategory(string $category);
    public function getTeasByType(string $type);
    public function getTeasByGrade(string $grade);
    public function searchTeas(string $query);
    public function getTeaStatistics();
    public function updateStatus(int $id, bool $status);
    public function checkTeaCombinationExists(array $data, int $excludeId = null);
}