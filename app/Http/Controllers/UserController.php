<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    // DEACTIVATING THE USER BUT NOT FULLY DELETED
    public function destroy(string $id)
    {
        //
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
                    <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Edit">
                        <i class="bi bi-pencil" style="font-size:13px;"></i>
                    </button>
                    <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Assign Role">
                        <i class="bi bi-shield-fill" style="font-size:13px;"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger" style="border-radius:6px;padding:3px 8px;" title="Deactivate">
                        <i class="bi bi-person-x-fill" style="font-size:13px;"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
