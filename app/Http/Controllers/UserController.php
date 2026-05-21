<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Official;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('users.view');

        $total_users = User::count();
        $active_users = User::where('status', '=', 'Active')->count();
        $total_admins = User::whereHas('official.role', fn ($query) => $query->where('role_name', 'Admin'))->count();
        $total_actions_today = ActivityLog::whereBetween('created_at', [
            Carbon::today()->startOfDay(),
            Carbon::today()->endOfDay(),
        ])->count();

        return view('users.index', compact('total_users', 'active_users', 'total_admins', 'total_actions_today'));
    }

    public function create()
    {
        $this->authorize('users.view');

        $roles = Role::orderBy('role_name')->get();
        $officials = Official::with(['resident', 'role'])
            ->whereDoesntHave('user')
            ->where('is_active', true)
            ->orderBy('official_number')
            ->get();

        return view('users.create', compact('roles', 'officials'));
    }

    public function store(Request $request)
    {
        $this->authorize('users.view');

        $validated = $request->validate([
            'official_id' => ['required', 'exists:officials,id', 'unique:users,official_id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        if (! empty($validated['role_id'])) {
            Official::whereKey($validated['official_id'])->update(['role_id' => $validated['role_id']]);
        }

        User::create([
            'official_id' => $validated['official_id'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'Active',
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'New user created successfully.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $this->authorize('users.view');

        $user = User::with('official.resident', 'official.role')->findOrFail($id);
        $roles = Role::orderBy('role_name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $this->authorize('users.view');

        $user = User::with('official')->findOrFail($id);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        $user->update(['email' => $validated['email']]);

        if ($user->official && ! empty($validated['role_id'])) {
            $user->official->update(['role_id' => $validated['role_id']]);
        }

        return redirect()
            ->route('users.edit', $user->id)
            ->with('success', 'User updated successfully.');
    }

    // DEACTIVATING THE USER BUT NOT FULLY DELETED
    public function destroy(string $id)
    {
        $this->authorize('users.view');

        $user = User::findOrFail($id);

        $user->update([
            'status' => 'Inactive',
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User deactivated successfully.');
    }

    public function getUsers(Request $request)
    {
        $this->authorize('users.view');

        $users = User::query()->with('official.resident', 'official.role');

        return DataTables::of($users)
            ->addColumn('first_name', fn ($user) => e($user->official?->resident?->first_name ?? ''))
            ->addColumn('middle_name', fn ($user) => e($user->official?->resident?->middle_name ?? ''))
            ->addColumn('last_name', fn ($user) => e($user->official?->resident?->last_name ?? ''))
            ->addColumn('role_name', fn ($user) => e($user->official?->role?->role_name ?? 'No role'))
            ->editColumn('last_accessed', function ($user) {
                return $user->last_accessed
                    ? $user->last_accessed->diffForHumans()
                    : 'Never';
            })
            ->addColumn('status', function ($user) {
                if ($user->status === 'Active') {
                    return '<span class="badge p-2 py- bg-success">Active</span>';
                } elseif ($user->status === 'Inactive') {
                    return '<span class="badge p-2 py- bg-danger">Inactive</span>';
                } else {
                    return '<span class="badge p-2 py- bg-secondary">Unknown</span>';
                }
            })
            ->addColumn('action', function ($user) {
                return '<div class="d-flex gap-1">
                    <a href="'.route('users.edit', $user->id).'" class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Edit">
                        <i class="bi bi-pencil" style="font-size:13px;"></i>
                    </a>
                    <a href="'.route('users.edit', ['user' => $user->id, 'section' => 'role']).'" class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Assign Role">
                        <i class="bi bi-shield-fill" style="font-size:13px;"></i>
                    </a>
                    <a href="'.route('users.edit', ['user' => $user->id, 'section' => 'deactivate']).'" class="btn btn-sm btn-light text-danger" style="border-radius:6px;padding:3px 8px;" title="Deactivate">
                        <i class="bi bi-person-x-fill" style="font-size:13px;"></i>
                    </a>
                </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
