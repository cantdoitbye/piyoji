<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserLoginLog;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $user,
        protected UserLoginLog $loginLog
    ) {}

    public function all(): Collection
    {
        return $this->user->with(['creator', 'updater'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->user->with(['creator', 'updater'])
            ->latest()
            ->paginate($perPage);
    }

    public function find($id): ?User
    {
        return $this->user->with(['creator', 'updater', 'loginLogs'])
            ->find($id);
    }

    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->user->create($data);
    }

    public function update($id, array $data): bool
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $user->update($data);
    }

    public function delete($id): bool
    {
        $user = $this->find($id);
        return $user ? $user->delete() : false;
    }

    public function restore($id): bool
    {
        $user = $this->user->withTrashed()->find($id);
        return $user ? $user->restore() : false;
    }

    public function findByEmail($email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    public function updateLastLogin($id): bool
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }

        return $user->update(['last_login_at' => now()]);
    }

    public function getActiveUsers(): Collection
    {
        return $this->user->where('status', 'active')->get();
    }

    public function getUsersByRole(string $role): Collection
    {
        return $this->user->where('role', $role)->get();
    }

    public function getUsersByDepartment(string $department): Collection
    {
        return $this->user->where('department', $department)->get();
    }

    public function updateStatus($id, string $status): bool
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }

        return $user->update(['status' => $status]);
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return $this->user->whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    public function searchUsers(string $query): Collection
    {
        return $this->user->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('department', 'like', "%{$query}%");
        })->get();
    }

    public function getUserStats(): array
    {
        $total = $this->user->count();
        $active = $this->user->where('status', 'active')->count();
        $inactive = $this->user->where('status', 'inactive')->count();
        
        $roleStats = $this->user->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        $departmentStats = $this->user->selectRaw('department, count(*) as count')
            ->whereNotNull('department')
            ->groupBy('department')
            ->pluck('count', 'department')
            ->toArray();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'roles' => $roleStats,
            'departments' => $departmentStats,
        ];
    }

    public function getRecentLogins(int $limit = 10): Collection
    {
        return $this->loginLog->with('user')
            ->latest('login_at')
            ->limit($limit)
            ->get();
    }

    public function createLoginLog(array $data): UserLoginLog
    {
        return $this->loginLog->create($data);
    }

    public function updateLogoutTime($userId): bool
    {
        $latestLog = $this->loginLog->where('user_id', $userId)
            ->whereNull('logout_at')
            ->latest('login_at')
            ->first();

        if ($latestLog) {
            return $latestLog->update(['logout_at' => now()]);
        }

        return false;
    }
}