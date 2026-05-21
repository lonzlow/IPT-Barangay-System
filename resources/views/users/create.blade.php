@extends('layouts.app')

@section('title', 'Add User – Barangay Management System')
@section('page-title', 'Add New User')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h5 class="mb-0">Create User Account</h5>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header fw-semibold">User Information</div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Linked Official</label>
                    <select name="official_id" class="form-select" required>
                        <option value="">Select official</option>
                        @foreach($officials as $official)
                            @php
                                $resident = $official->resident;
                                $name = $resident ? trim($resident->last_name . ', ' . $resident->first_name . ' ' . $resident->middle_name . ' ' . $resident->suffix) : $official->official_number;
                            @endphp
                            <option value="{{ $official->id }}" @selected(old('official_id') == $official->id)>
                                {{ $name }} ({{ $official->role?->role_name ?? 'No role' }})
                            </option>
                        @endforeach
                    </select>
                    @if($officials->isEmpty())
                        <div class="form-text text-muted">
                            All active officials already have user accounts. Add a new official profile first, or edit an existing user account instead.
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select">
                        <option value="">Keep official role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary" @disabled($officials->isEmpty())>
                    <i class="bi bi-person-plus-fill"></i> Create User
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
