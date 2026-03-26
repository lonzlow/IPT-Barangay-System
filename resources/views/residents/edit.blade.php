@extends('layouts.app')

@section('title', 'Edit Resident – Barangay Management System')
@section('page-title', 'Edit Resident')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h5 class="mb-0">Resident Details</h5>
    <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Residents
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
@endphp

<div class="card mb-3 {{ $section === 'status' ? 'border-primary' : '' }}">
    <div class="card-header fw-semibold">Edit Resident</div>
    <div class="card-body">
        <form method="POST" action="{{ route('residents.update', $resident->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $resident->first_name) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $resident->middle_name) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $resident->last_name) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Suffix</label>
                    <input type="text" name="suffix" class="form-control" value="{{ old('suffix', $resident->suffix) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $resident->email) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $resident->contact_number) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Birthdate</label>
                    <input type="date" name="birthdate" class="form-control" value="{{ old('birthdate', $resident->birthdate) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        @foreach(['Male', 'Female', 'Other'] as $option)
                            <option value="{{ $option }}" @selected(old('gender', $resident->gender) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Civil Status</label>
                    <select name="civil_status" class="form-select" required>
                        @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Divorced'] as $option)
                            <option value="{{ $option }}" @selected(old('civil_status', $resident->civil_status) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Voter Status</label>
                    <select name="voter_status" class="form-select" required>
                        @foreach(['Registered', 'Unregistered', 'Suspended'] as $option)
                            <option value="{{ $option }}" @selected(old('voter_status', $resident->voter_status) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Residency Status</label>
                    <select name="residency_status" class="form-select" required>
                        @foreach(['Active', 'Deceased', 'Transferred'] as $option)
                            <option value="{{ $option }}" @selected(old('residency_status', $resident->residency_status) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Household</label>
                    <select name="household_id" class="form-select" required>
                        @foreach($households as $household)
                            <option value="{{ $household->id }}" @selected(old('household_id', $resident->household_id) == $household->id)>
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
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
                <a href="{{ route('residents.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-danger {{ $section === 'deactivate' ? 'border-2' : '' }}">
    <div class="card-header text-danger fw-semibold">Deactivate Resident</div>
    <div class="card-body">
        <p class="mb-3 text-secondary">This will archive the resident record from active list.</p>
        <form method="POST" action="{{ route('residents.destroy', $resident->id) }}" onsubmit="return confirm('Deactivate this resident?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-person-x-fill"></i> Deactivate Resident
            </button>
        </form>
    </div>
</div>
@endsection
