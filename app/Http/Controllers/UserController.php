<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
        $total_users = User::all()->count();
        $active_users = User::where('status', '=', 'Active')->count();
        $total_admins = User::where('role_id', '=', 1)->count(); // Role id 1 should always be admin
        $total_actions_today = ActivityLog::whereBetween('created_at', [
            Carbon::today()->startOfDay(),
            Carbon::today()->endOfDay(),
        ])->count();

        return view('users.index', compact('total_users', 'active_users', 'total_admins', 'total_actions_today'));
    }

    public function create()
    {
        $roles = Role::orderBy('role_name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => $validated['role_id'] ?? null,
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
        $user = User::findOrFail($id);
        $roles = Role::orderBy('role_name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        $user->update($validated);

        return redirect()
            ->route('users.edit', $user->id)
            ->with('success', 'User updated successfully.');
    }

    // DEACTIVATING THE USER BUT NOT FULLY DELETED
    public function destroy(string $id)
    {
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
        $users = User::query();

        return DataTables::of($users)
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
