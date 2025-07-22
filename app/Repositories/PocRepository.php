<?php

namespace App\Repositories;

use App\Models\Poc;
use App\Repositories\Interfaces\PocRepositoryInterface;

class PocRepository extends BaseRepository implements PocRepositoryInterface
{
    public function __construct(Poc $model)
    {
        parent::__construct($model);
    }

    public function getActivePocsList()
    {
        return $this->model->active()
            ->select('id', 'poc_name', 'poc_type', 'email', 'phone')
            ->orderBy('poc_name')
            ->get();
    }

    public function getPocsByType(string $type)
    {
        return $this->model->active()
            ->byType($type)
            ->orderBy('poc_name')
            ->get();
    }

    public function searchPocs(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('poc_name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%")
              ->orWhere('designation', 'LIKE', "%{$query}%")
              ->orWhere('city', 'LIKE', "%{$query}%")
              ->orWhere('state', 'LIKE', "%{$query}%");
        })->get();
    }

    public function getPocStatistics()
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
            'sellers' => $this->model->forSellers()->count(),
            'buyers' => $this->model->forBuyers()->count(),
            'both' => $this->model->byType('both')->count(),
            'recent' => $this->model->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function checkEmailExists(string $email, int $excludeId = null)
    {
        $query = $this->model->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function getPocsByStatus(bool $status)
    {
        return $this->model->where('status', $status)
            ->orderBy('poc_name')
            ->get();
    }
}