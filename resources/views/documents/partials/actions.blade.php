<div class="d-flex gap-2">
    <a href="{{ route('documents.show', $document->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:6px;padding:4px 8px;" title="View">
        <i class="bi bi-eye"></i>
    </a>
    <button type="button" class="btn btn-sm btn-outline-warning" style="border-radius:6px;padding:4px 8px;" title="Edit" onclick="openEditModal('{{ $document->id }}')">
        <i class="bi bi-pencil"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius:6px;padding:4px 8px;" title="Delete" onclick="deleteDocument('{{ $document->id }}')">
        <i class="bi bi-trash"></i>
    </button>
    <a href="{{ route('documents.downloadPdf', $document->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:6px;padding:4px 8px;" title="Download PDF">
        <i class="bi bi-download"></i>
    </a>
</div>
