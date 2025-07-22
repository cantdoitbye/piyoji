<?php

namespace App\Repositories;

use App\Models\Garden;
use App\Repositories\Interfaces\GardenRepositoryInterface;

class GardenRepository extends BaseRepository implements GardenRepositoryInterface
{
    public function __construct(Garden $model)
    {
        parent::__construct($model);
    }

    public function getActiveGardensList()
    {
        return $this->model->active()
            ->select('id', 'garden_name', 'contact_person_name', 'mobile_no', 'state')
            ->orderBy('garden_name')
            ->get();
    }

    public function getGardensByState(string $state)
    {
        return $this->model->active()
            ->byState($state)
            ->orderBy('garden_name')
            ->get();
    }

    public function getGardensByTeaId(int $teaId)
    {
        return $this->model->active()
            ->whereJsonContains('tea_ids', $teaId)
            ->orderBy('garden_name')
            ->get();
    }

    public function searchGardens(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('garden_name', 'LIKE', "%{$query}%")
              ->orWhere('contact_person_name', 'LIKE', "%{$query}%")
              ->orWhere('mobile_no', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('city', 'LIKE', "%{$query}%")
              ->orWhere('state', 'LIKE', "%{$query}%")
              ->orWhere('speciality', 'LIKE', "%{$query}%");
        })->get();
    }

    public function getGardenStatistics()
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
            'states' => $this->model->distinct('state')->whereNotNull('state')->count('state'),
            'with_speciality' => $this->model->whereNotNull('speciality')->count(),
            'recent' => $this->model->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function getGardenWithTeas(int $id)
    {
        $garden = $this->model->find($id);
        if ($garden && $garden->tea_ids) {
            $garden->load(['selectedTeas']);
        }
        return $garden;
    }

    public function getGardensByStatus(bool $status)
    {
        return $this->model->where('status', $status)
            ->orderBy('garden_name')
            ->get();
    }

    public function getUniqueStates()
    {
        return $this->model->distinct('state')
            ->whereNotNull('state')
            ->orderBy('state')
            ->pluck('state');
    }
}