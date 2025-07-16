<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function all();
    public function paginate(int $perPage = 15);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function restore($id);
    public function findByEmail($email);
    public function updateLastLogin($id);
    public function getActiveUsers();
    public function getUsersByRole(string $role);
    public function getUsersByDepartment(string $department);
    public function updateStatus($id, string $status);
    public function bulkUpdateStatus(array $ids, string $status);
    public function searchUsers(string $query);
    public function getUserStats();
    public function getRecentLogins(int $limit = 10);
    public function createLoginLog(array $data);
    public function updateLogoutTime($userId);
}