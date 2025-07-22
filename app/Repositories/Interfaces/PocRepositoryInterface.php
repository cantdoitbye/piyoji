<?php

namespace App\Repositories\Interfaces;

interface PocRepositoryInterface extends BaseRepositoryInterface
{
        public function getModel();
    public function getActivePocsList();
    public function getPocsByType(string $type);
    public function searchPocs(string $query);
    public function getPocStatistics();
    public function updateStatus(int $id, bool $status);
    public function checkEmailExists(string $email, int $excludeId = null);
}