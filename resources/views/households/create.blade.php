@extends('layouts.app')

@section('title', 'Create Household')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Register New Household</h1>
                <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-4">
                    <form action="{{ route('households.store') }}" method="POST">
                        @csrf

                        {{-- Purok Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-map"></i> Purok <span class="text-danger">*</span>
                            </label>
                            <select name="purok_id" class="form-select form-select-lg" style="border-radius:8px;" required>
                                <option value="">-- Select Purok --</option>
                                @foreach($puroks as $purok)
                                    <option value="{{ $purok->id }}" {{ old('purok_id') == $purok->id ? 'selected' : '' }}>
                                        {{ $purok->purok_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('purok_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- House Number --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-house"></i> House Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="house_number" class="form-control form-control-lg" style="border-radius:8px;" value="{{ old('house_number') }}" required>
                            @error('house_number')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Street --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-street-view"></i> Street <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="street" class="form-control form-control-lg" style="border-radius:8px;" value="{{ old('street') }}" required>
                            @error('street')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Family Size --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-people"></i> Family Size <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="family_size" class="form-control form-control-lg" style="border-radius:8px;" value="{{ old('family_size', 1) }}" min="1" required>
                            @error('family_size')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Head of Household --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-person-check"></i> Head of Household
                            </label>
                            <select name="head_resident_id" class="form-select form-select-lg" style="border-radius:8px;">
                                <option value="">-- Select Head --</option>
                                @foreach($residents as $resident)
                                    <option value="{{ $resident->id }}" {{ old('head_resident_id') == $resident->id ? 'selected' : '' }}>
                                        {{ $resident->first_name }} {{ $resident->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small style="color:#94a3b8;">Optional - can be assigned later</small>
                            @error('head_resident_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex gap-2 pt-3">
                            <button type="submit" class="btn btn-primary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-check-circle"></i> Create Household
                            </button>
                            <a href="{{ route('households.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Help Sidebar --}}
        <div class="col-lg-4">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background-color:#f8fafc;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3" style="color:#1e293b;">
                        <i class="bi bi-lightbulb"></i> Tips
                    </h5>
                    <div style="font-size:13px;color:#475569;line-height:1.6;">
                        <p><strong>House Number:</strong> Use a unique identifier (e.g., "101", "B-45", "Lot 5")</p>
                        <p><strong>Family Size:</strong> Total number of family members living in this household</p>
                        <p><strong>Head of Household:</strong> The primary resident - can be changed anytime</p>
                        <p class="text-muted">All fields marked with <span class="text-danger">*</span> are required</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
