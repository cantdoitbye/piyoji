<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected $model;
    
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
    
    public function all(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }
    
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }
    
    public function find(int $id, array $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }
    
    public function findBy(string $field, $value, array $columns = ['*'])
    {
        return $this->model->where($field, $value)->first($columns);
    }
    
    public function findWhere(array $where, array $columns = ['*'])
    {
        return $this->model->where($where)->get($columns);
    }
    
    public function create(array $data)
    {
        return $this->model->create($data);
    }
    
    public function update(int $id, array $data)
    {
        $model = $this->find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }
    
    public function delete(int $id)
    {
        $model = $this->find($id);
        if ($model) {
            return $model->delete();
        }
        return false;
    }
    
    public function deleteWhere(array $where)
    {
        return $this->model->where($where)->delete();
    }
    
    public function count()
    {
        return $this->model->count();
    }
    
    public function with(array $relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }
    
    public function whereHas(string $relation, callable $callback)
    {
        $this->model = $this->model->whereHas($relation, $callback);
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);
        return $this;
    }
    
    public function search(string $query, array $columns = [])
    {
        $builder = $this->model->newQuery();
        
        foreach ($columns as $column) {
            $builder->orWhere($column, 'LIKE', "%{$query}%");
        }
        
        return $builder->get();
    }
    
    protected function resetModel()
    {
        $this->model = $this->model->newQuery();
    }
}