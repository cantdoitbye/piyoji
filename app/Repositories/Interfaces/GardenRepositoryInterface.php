<?php

namespace App\Repositories\Interfaces;

interface GardenRepositoryInterface extends BaseRepositoryInterface
{
        public function getModel();
    public function getActiveGardensList();
    public function getGardensByState(string $state);
    public function getGardensByTeaId(int $teaId);
    public function searchGardens(string $query);
    public function getGardenStatistics();
    public function updateStatus(int $id, bool $status);
    public function getGardenWithTeas(int $id);
}