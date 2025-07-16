<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class UserController extends BaseAdminController
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Display a listing of users
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'role', 'status', 'department']);
        
        $query = \App\Models\User::with(['creator', 'updater']);
        
        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }
        
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }
        
        $users = $query->latest()->paginate(15)->withQueryString();
        $stats = $this->userRepository->getUserStats();
        
        // Check if request wants JSON response (for AJAX calls)
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users->items(),
                    'stats' => $stats,
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                    ]
                ]
            ]);
        }
        
        return view('admin.users.index', compact('users', 'stats', 'filters'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): View
    {
        $departments = $this->getDepartments();
        $permissions = $this->getAvailablePermissions();
        
        return view('admin.users.create', compact('departments', 'permissions'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // Use the abstract method for validation
        $data = $this->validateStore($request);
        $data['created_by'] = auth('admin')->id();

        try {
            $user = $this->userRepository->create($data);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully.',
                    'data' => ['user' => $user]
                ]);
            }
            
            return redirect()->route('admin.users.show', $user->id)
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show(int $id): View|JsonResponse
    {
        $request = request(); // Get the current request instance
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.'
                ], 404);
            }
            
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }
        
        $recentLogins = $user->loginLogs()->latest('login_at')->limit(10)->get();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'recent_logins' => $recentLogins
                ]
            ]);
        }
        
        return view('admin.users.show', compact('user', 'recentLogins'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(int $id): View
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }
        
        $departments = $this->getDepartments();
        $permissions = $this->getAvailablePermissions();
        
        return view('admin.users.edit', compact('user', 'departments', 'permissions'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.'
                ], 404);
            }
            
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }

        // Use the abstract method for validation
        $data = $this->validateUpdate($request, $id);
        $data['updated_by'] = auth('admin')->id();

        try {
            $this->userRepository->update($id, $data);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully.'
                ]);
            }
            
            return redirect()->route('admin.users.show', $id)
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(int $id): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request = request(); // Get the current request instance
        
        try {
            $this->userRepository->delete($id);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully.'
                ]);
            }
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $this->userRepository->updateStatus($id, $request->status);
            
            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk action for users
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            $count = 0;
            
            switch ($request->action) {
                case 'activate':
                    $count = $this->userRepository->bulkUpdateStatus($request->user_ids, 'active');
                    break;
                case 'deactivate':
                    $count = $this->userRepository->bulkUpdateStatus($request->user_ids, 'inactive');
                    break;
                case 'delete':
                    foreach ($request->user_ids as $id) {
                        $this->userRepository->delete($id);
                        $count++;
                    }
                    break;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$count} users."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users for AJAX
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $users = $this->userRepository->searchUsers($query);
        
        return response()->json($users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role_text,
                'status' => $user->status_text,
                'department' => $user->department,
            ];
        }));
    }

    /**
     * Get users for select dropdown
     */
    public function getForSelect(Request $request): JsonResponse
    {
        $role = $request->get('role');
        $department = $request->get('department');
        
        $query = \App\Models\User::active();
        
        if ($role) {
            $query->where('role', $role);
        }
        
        if ($department) {
            $query->where('department', $department);
        }
        
        $users = $query->select('id', 'name', 'email', 'role', 'department')
            ->orderBy('name')
            ->get();
            
        return response()->json($users);
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'role', 'status', 'department']);
        
        $query = \App\Models\User::with(['creator', 'updater']);
        
        // Apply same filters as index
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }
        
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }
        
        $users = $query->latest()->get();
        
        $filename = 'users_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add CSV header
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Role',
                'Status',
                'Department',
                'Permissions',
                'Last Login',
                'Created At',
                'Created By'
            ]);
            
            // Add data rows
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->role_text,
                    $user->status_text,
                    $user->department,
                    implode(', ', $user->permissions ?? []),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->creator ? $user->creator->name : 'System'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get available departments
     */
    private function getDepartments(): array
    {
        return [
            'Sample Management',
            'Quality Control',
            'Dispatch',
            'Data Entry',
            'Administration',
            'Finance',
            'Sales',
            'Operations'
        ];
    }

    /**
     * Get available permissions
     */
    private function getAvailablePermissions(): array
    {
        return [
            'manage_samples' => 'Manage Samples',
            'view_samples' => 'View Samples',
            'manage_sellers' => 'Manage Sellers',
            'view_sellers' => 'View Sellers',
            'manage_buyers' => 'Manage Buyers',
            'view_buyers' => 'View Buyers',
            'manage_contracts' => 'Manage Contracts',
            'view_contracts' => 'View Contracts',
            'manage_dispatch' => 'Manage Dispatch',
            'view_dispatch' => 'View Dispatch',
            'generate_reports' => 'Generate Reports',
            'view_reports' => 'View Reports',
            'manage_feedback' => 'Manage Buyer Feedback',
            'view_feedback' => 'View Buyer Feedback',
        ];
    }

    /**
     * Get the permission prefix for this controller
     * Required by BaseAdminController
     */
    protected function getPermissionPrefix(): string
    {
        return 'users';
    }

    /**
     * Validate store request
     * Required by BaseAdminController
     */
    protected function validateStore(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:data_entry,supervisor,viewer',
            'status' => 'required|in:active,inactive',
            'department' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);
    }

    /**
     * Validate update request
     * Required by BaseAdminController
     */
    protected function validateUpdate(Request $request, int $id): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:data_entry,supervisor,viewer',
            'status' => 'required|in:active,inactive',
            'department' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);
    }
}