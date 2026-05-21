@extends('layouts.app')

@section('title', 'Create Household')
@section('page-title', 'Create Household')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h5 class="fw-800 mb-1" style="font-size:18px;">Create Household</h5>
        <p class="mb-0" style="font-size:13px;color:#64748b;">Add a household address and assign it to a Purok.</p>
    </div>
    <a href="{{ route('households.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;">
            <div class="card-body p-4">
                <form action="{{ route('households.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-600">Purok <span class="text-danger">*</span></label>
                        <select name="purok_id" class="form-select @error('purok_id') is-invalid @enderror" required>
                            <option value="">Select Purok</option>
                            @foreach($puroks as $purok)
                                <option value="{{ $purok->id }}" @selected(old('purok_id') == $purok->id)>{{ $purok->purok_name }}</option>
                            @endforeach
                        </select>
                        @error('purok_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">House Number <span class="text-danger">*</span></label>
                        <input type="text" name="house_number" value="{{ old('house_number') }}" class="form-control @error('house_number') is-invalid @enderror" required>
                        @error('house_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Street <span class="text-danger">*</span></label>
                        <input type="text" name="street" value="{{ old('street') }}" class="form-control @error('street') is-invalid @enderror" required>
                        @error('street')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-primary" style="border-radius:8px;">
                            <i class="bi bi-check-circle"></i> Create Household
                        </button>
                        <a href="{{ route('households.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
