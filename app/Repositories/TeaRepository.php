<?php

namespace App\Repositories;

use App\Models\Tea;
use App\Repositories\Interfaces\TeaRepositoryInterface;

class TeaRepository extends BaseRepository implements TeaRepositoryInterface
{
    public function __construct(Tea $model)
    {
        parent::__construct($model);
    }

    public function getActiveTeasList()
    {
        return $this->model->active()
            ->select('id', 'category', 'tea_type', 'sub_title', 'grade')
            ->orderBy('category')
            ->orderBy('tea_type')
            ->orderBy('sub_title')
            ->get();
    }

    public function getTeasByCategory(string $category)
    {
        return $this->model->active()
            ->byCategory($category)
            ->orderBy('tea_type')
            ->orderBy('sub_title')
            ->get();
    }

    public function getTeasByType(string $type)
    {
        return $this->model->active()
            ->byTeaType($type)
            ->orderBy('category')
            ->orderBy('sub_title')
            ->get();
    }

    public function getTeasByGrade(string $grade)
    {
        return $this->model->active()
            ->byGrade($grade)
            ->orderBy('category')
            ->orderBy('tea_type')
            ->get();
    }

    public function searchTeas(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('category', 'LIKE', "%{$query}%")
              ->orWhere('tea_type', 'LIKE', "%{$query}%")
              ->orWhere('sub_title', 'LIKE', "%{$query}%")
              ->orWhere('grade', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%");
        })->get();
    }

    public function getTeaStatistics()
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
            'categories' => $this->model->distinct('category')->count('category'),
            'tea_types' => $this->model->distinct('tea_type')->count('tea_type'),
            'grades' => $this->model->distinct('grade')->count('grade'),
            'recent' => $this->model->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function checkTeaCombinationExists(array $data, int $excludeId = null)
    {
        $query = $this->model->where('category', $data['category'])
            ->where('tea_type', $data['tea_type'])
            ->where('sub_title', $data['sub_title'])
            ->where('grade', $data['grade']);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function getTeasByStatus(bool $status)
    {
        return $this->model->where('status', $status)
            ->orderBy('category')
            ->orderBy('tea_type')
            ->orderBy('sub_title')
            ->get();
    }

    public function getUniqueCategories()
    {
        return $this->model->distinct('category')
            ->orderBy('category')
            ->pluck('category');
    }

    public function getUniqueTeaTypes()
    {
        return $this->model->distinct('tea_type')
            ->orderBy('tea_type')
            ->pluck('tea_type');
    }

    public function getUniqueGrades()
    {
        return $this->model->distinct('grade')
            ->orderBy('grade')
            ->pluck('grade');
    }
}