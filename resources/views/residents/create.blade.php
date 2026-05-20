@extends('layouts.app')

@section('title', 'Add Resident – Barangay Management System')
@section('page-title', 'Add New Resident')

@section('content')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h5 class="mb-0">Create Resident Record</h5>
        <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Residents
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
        <div class="card-header fw-semibold">Resident Information</div>
        <div class="card-body">
            <form method="POST" action="{{ route('residents.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Suffix</label>
                        <input type="text" name="suffix" class="form-control" value="{{ old('suffix') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Birthdate</label>
                        <input type="date" name="birthdate" class="form-control" value="{{ old('birthdate') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select gender</option>
                            @foreach(['Male', 'Female'] as $option)
                                <option value="{{ $option }}" @selected(old('gender') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Civil Status</label>
                        <select name="civil_status" class="form-select" required>
                            <option value="">Select status</option>
                            @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Divorced'] as $option)
                                <option value="{{ $option }}" @selected(old('civil_status') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Voter Status</label>
                        <select name="voter_status" class="form-select" required>
                            @foreach(['Registered', 'Unregistered', 'Suspended'] as $option)
                                <option value="{{ $option }}" @selected(old('voter_status', 'Unregistered') === $option)>
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Residency Status</label>
                        <select name="residency_status" class="form-select" required>
                            @foreach(['Active', 'Deceased', 'Transferred'] as $option)
                                <option value="{{ $option }}" @selected(old('residency_status', 'Active') === $option)>
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Household</label>
                        <select name="household_id" class="form-select" required>
                            <option value="">Select household</option>
                            @foreach($households as $household)
                                <option value="{{ $household->id }}" @selected(old('household_id') == $household->id)>
                                    #{{ $household->house_number }} {{ $household->street }}
                                    @if($household->purok)
                                        - {{ $household->purok->purok_name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill"></i> Create Resident
                    </button>
                    <a href="{{ route('residents.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection