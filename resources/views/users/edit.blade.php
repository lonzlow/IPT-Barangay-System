@extends('layouts.app')

@section('title', 'Edit User – Barangay Management System')
@section('page-title', 'Edit User')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h5 class="mb-0">User Details</h5>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $section = request('section');
    $resident = $user->official?->resident;
    $officialName = $resident ? trim($resident->last_name . ', ' . $resident->first_name . ' ' . $resident->middle_name . ' ' . $resident->suffix) : 'No linked official';
@endphp

<div class="card mb-3 {{ $section === 'role' ? 'border-primary' : '' }}">
    <div class="card-header fw-semibold">Edit / Assign Role</div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Linked Official</label>
                    <input type="text" class="form-control" value="{{ $officialName }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select">
                        <option value="">Keep current role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->official?->role_id) == $role->id)>
                                {{ $role->role_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-danger {{ $section === 'deactivate' ? 'border-2' : '' }}">
    <div class="card-header text-danger fw-semibold">Deactivate User</div>
    <div class="card-body">
        <p class="mb-3 text-secondary">This will set this account status to <strong>Inactive</strong>.</p>
        <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('Deactivate this user?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-person-x-fill"></i> Deactivate User
            </button>
        </form>
    </div>
</div>
@endsection
