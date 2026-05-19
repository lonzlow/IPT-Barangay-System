@extends('layouts.app')

@section('title', 'Certificate Templates')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fw-bold" style="font-size:28px;">Certificate Templates</h1>
                <a href="{{ route('certificate-templates.create') }}" class="btn btn-primary" style="border-radius:8px;padding:10px 20px;">
                    <i class="bi bi-plus-circle"></i> Create Template
                </a>
            </div>
        </div>
    </div>

    {{-- Templates Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:14px;">
                            <thead style="background:#f9fafb;border-bottom:1px solid #e2e8f0;">
                                <tr>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Template Name</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Description</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Validity (Days)</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Status</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Documents Issued</th>
                                    <th style="padding:12px 16px;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td style="padding:12px 16px;">
                                            <strong>{{ $template->name }}</strong>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <small style="color:#64748b;">{{ Str::limit($template->description, 50) }}</small>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            {{ $template->validity_days ?? 'N/A' }}
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @if($template->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span class="badge bg-light text-dark">{{ $template->documents()->count() }}</span>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('certificate-templates.show', $template->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:6px;padding:4px 8px;">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ route('certificate-templates.edit', $template->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:6px;padding:4px 8px;">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                @if($template->documents()->count() === 0)
                                                    <form action="{{ route('certificate-templates.destroy', $template->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');" style="border-radius:6px;padding:4px 8px;">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4" style="color:#94a3b8;">
                                            <i class="bi bi-inbox" style="font-size:32px;opacity:0.3;display:block;margin-bottom:8px;"></i>
                                            No templates found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer" style="background:#f9fafb;border-top:1px solid #e2e8f0;">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
