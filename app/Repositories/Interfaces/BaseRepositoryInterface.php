<?php

namespace App\Repositories\Interfaces;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']);
    
    public function paginate(int $perPage = 15, array $columns = ['*']);
    
    public function find($id, array $columns = ['*']);
    
    public function findBy(string $field, $value, array $columns = ['*']);
    
    public function findWhere(array $where, array $columns = ['*']);
    
    public function create(array $data);
    
    public function update(int $id, array $data);
    
    public function delete(int $id);
    
    public function deleteWhere(array $where);
    
    public function count();
    
    public function with(array $relations);
    
    public function whereHas(string $relation, callable $callback);
    
    public function orderBy(string $column, string $direction = 'asc');
    
    public function search(string $query, array $columns = []);
}