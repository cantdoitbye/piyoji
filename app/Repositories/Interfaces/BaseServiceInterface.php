<?php

namespace App\Services\Interfaces;

interface BaseServiceInterface
{
    public function index(array $filters = []);
    
    public function show(int $id);
    
    public function store(array $data);
    
    public function update(int $id, array $data);
    
    public function destroy(int $id);
    
    public function search(string $query);
    
    public function getForSelect();
}