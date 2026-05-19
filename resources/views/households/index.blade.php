@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Households</h3>
                    <button type="button" class="btn btn-primary" onclick="createHousehold()">
                        Add Household
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="households-table">
                        <thead>
                            <tr>
                                <th>Purok</th>
                                <th>Head Name</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($households as $household)
                            <tr id="row-{{ $household->id }}">
                                <td>{{ $household->purok->purok_name ?? 'N/A' }}</td>
                                <td>{{ $household->head_first_name }} {{ $household->head_middle_name }} {{ $household->head_last_name }}</td>
                                <td>{{ $household->head_contact_number }}</td>
                                <td>
                                    @if($household->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="editHousehold({{ $household->id }})">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteHousehold({{ $household->id }})">Delete</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="householdModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="householdForm">
            @csrf
            <input type="hidden" name="id" id="household_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Household</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Purok</label>
                        <select name="purok_id" id="purok_id" class="form-select" required>
                            @foreach($puroks as $purok)
                                <option value="{{ $purok->id }}">{{ $purok->purok_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>First Name</label>
                        <input type="text" name="head_first_name" id="head_first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Middle Name</label>
                        <input type="text" name="head_middle_name" id="head_middle_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Last Name</label>
                        <input type="text" name="head_last_name" id="head_last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contact Number</label>
                        <input type="text" name="head_contact_number" id="head_contact_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Notes</label>
                        <textarea name="notes" id="notes" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const householdModal = new bootstrap.Modal(document.getElementById('householdModal'));

function createHousehold() {
    document.getElementById('householdForm').reset();
    document.getElementById('household_id').value = '';
    document.getElementById('modalTitle').textContent = 'Add Household';
    householdModal.show();
}

async function editHousehold(id) {
    const res = await fetch(`/households/${id}/edit`);
    const data = await res.json();
    if (data) {
        document.getElementById('household_id').value = data.id;
        document.getElementById('purok_id').value = data.purok_id;
        document.getElementById('head_first_name').value = data.head_first_name;
        document.getElementById('head_middle_name').value = data.head_middle_name || '';
        document.getElementById('head_last_name').value = data.head_last_name;
        document.getElementById('head_contact_number').value = data.head_contact_number || '';
        document.getElementById('notes').value = data.notes || '';
        document.getElementById('status').value = data.status;
        document.getElementById('modalTitle').textContent = 'Edit Household';
        householdModal.show();
    }
}

async function deleteHousehold(id) {
    if (!confirm('Are you sure you want to delete this household?')) return;
    try {
        const res = await fetch(`/households/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        if (res.ok) {
            document.getElementById(`row-${id}`).remove();
            alert('Household deleted successfully');
        } else {
            alert('Failed to delete household');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

document.getElementById('householdForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('household_id').value;
    const url = id ? `/households/${id}` : '/households';
    const method = id ? 'PUT' : 'POST';
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (res.ok) {
            alert('Household saved successfully');
            householdModal.hide();
            location.reload();
        } else {
            const errorData = await res.json();
            alert('Error: ' + JSON.stringify(errorData.errors || errorData.message));
        }
    } catch (err) {
        alert('An error occurred: ' + err.message);
    }
});
</script>
@endsection
