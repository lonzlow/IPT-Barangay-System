@extends('layouts.app')

@section('title', 'Edit Purok')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Edit Purok: {{ $purok->purok_name }}</h1>
                <a href="{{ route('puroks.show', $purok->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
                <div class="card-body p-4">
                    <form action="{{ route('puroks.update', $purok->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Purok Name --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-map"></i> Purok Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="purok_name" class="form-control form-control-lg" style="border-radius:8px;" value="{{ $purok->purok_name }}" required>
                            @error('purok_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-file-text"></i> Description
                            </label>
                            <textarea name="description" class="form-control" style="border-radius:8px;font-size:14px;" rows="3">{{ $purok->description }}</textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Purok Leader --}}
                        <div class="mb-4">
                            <label class="form-label fw-600" style="font-size:14px;color:#1e293b;">
                                <i class="bi bi-person-badge"></i> Purok Leader
                            </label>
                            <select name="leader_id" class="form-select form-select-lg" style="border-radius:8px;">
                                <option value="">-- No Leader Assigned --</option>
                                @foreach($residents as $resident)
                                    <option value="{{ $resident->id }}" {{ $purok->leader_id == $resident->id ? 'selected' : '' }}>
                                        {{ $resident->first_name }} {{ $resident->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leader_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex gap-2 pt-3">
                            <button type="submit" class="btn btn-primary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                            <a href="{{ route('puroks.show', $purok->id) }}" class="btn btn-outline-secondary" style="border-radius:8px;padding:10px 20px;">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info Sidebar --}}
        <div class="col-lg-4">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background-color:#f8fafc;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3" style="color:#1e293b;">
                        <i class="bi bi-info-circle"></i> Current Info
                    </h5>
                    <div style="font-size:13px;color:#475569;">
                        <p><strong>Households:</strong> {{ $purok->households()->count() }} household(s)</p>
                        <p><strong>Current Leader:</strong> {{ $purok->leader?->first_name ?? 'Not assigned' }} {{ $purok->leader?->last_name ?? '' }}</p>
                        <p><strong>Created:</strong> {{ $purok->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
