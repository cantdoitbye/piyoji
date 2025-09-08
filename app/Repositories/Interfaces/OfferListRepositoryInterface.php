<?php

namespace App\Repositories\Interfaces;

interface OfferListRepositoryInterface extends BaseRepositoryInterface
{
    public function getByGarden(int $gardenId);
    public function getByGrade(string $grade);
    public function getByType(string $type);
    public function getByDateRange(string $startDate, string $endDate);
    public function searchOffers(string $query);
    public function getOfferStatistics();
}