{{-- Document Form Modal Component --}}
@php
    $issuerResident = auth()->user()?->official?->resident;
    $defaultIssuer = $issuerResident
        ? trim($issuerResident->first_name . ' ' . $issuerResident->last_name)
        : (auth()->user()?->email ?? 'Barangay Official');
@endphp
<div class="modal fade" id="documentFormModal" tabindex="-1" aria-labelledby="documentFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #e2e8f0;">
                <h5 class="modal-title fw-bold" id="documentFormModalLabel">Issue New Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="documentForm">
                    <input type="hidden" id="documentId" value="">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="formMethod" value="POST">

                    {{-- Resident Selection --}}
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px;color:#1e293b;">
                            <i class="bi bi-person"></i> Resident
                        </label>
                        <input type="hidden" name="resident_id" id="residentIdInput" required>
                        <input type="text" class="form-control" id="residentSearchInput" style="border-radius:8px;font-size:13px;" placeholder="Type a resident name or number" autocomplete="off">
                        <select id="residentResults" class="form-select d-none mt-2" size="5"></select>
                        <small class="text-danger d-none" id="resident_id-error"></small>
                    </div>

                    {{-- Document Template Selection --}}
                    <div class="mb-3" id="templateGroup">
                        <label class="form-label fw-600" style="font-size:13px;color:#1e293b;">
                            <i class="bi bi-file-text"></i> Document Type
                        </label>
                        <select name="document_template_id" class="form-select" id="templateSelect" style="border-radius:8px;font-size:13px;" required>
                            <option value="">-- Select Document Type --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}"
                                    data-description="{{ $template->description }}"
                                    data-business="{{ str_contains(strtolower($template->name), 'business') ? '1' : '0' }}">
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                        <small id="template-desc" class="d-block mt-2" style="color:#64748b;font-size:12px;"></small>
                        <small class="text-danger d-none" id="document_template_id-error"></small>
                    </div>

                    {{-- Purpose (Optional) --}}
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px;color:#1e293b;">
                            <i class="bi bi-card-text"></i> Purpose of Issuance (Optional)
                        </label>
                        <input type="text" name="purpose" class="form-control" style="border-radius:8px;font-size:13px;" 
                               placeholder="e.g., For Employment, For Travel, etc.">
                        <small class="text-danger d-none" id="purpose-error"></small>
                    </div>

                    <div class="mb-3 d-none" id="businessGroup">
                        <label class="form-label fw-600" style="font-size:13px;color:#1e293b;">
                            <i class="bi bi-shop-window"></i> Business
                        </label>
                        <select name="business_id" class="form-select" id="businessSelect" style="border-radius:8px;font-size:13px;">
                            <option value="">-- Select Business --</option>
                        </select>
                        <small class="d-block mt-2" id="business-help" style="color:#64748b;font-size:12px;">Select a resident first to load their active businesses.</small>
                        <small class="text-danger d-none" id="business_id-error"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px;color:#1e293b;">
                            <i class="bi bi-journal-text"></i> Additional Notes (Optional)
                        </label>
                        <textarea name="additional_notes" class="form-control" rows="3" style="border-radius:8px;font-size:13px;resize:none;"
                            placeholder="Special instructions or inclusions to print in the document."></textarea>
                        <small class="text-danger d-none" id="additional_notes-error"></small>
                    </div>

                    {{-- Issued By --}}
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px;color:#1e293b;">
                            <i class="bi bi-pencil-square"></i> Issued By
                        </label>
                        <input type="text" name="issued_by" class="form-control" style="border-radius:8px;font-size:13px;" 
                               value="{{ $defaultIssuer }}" required>
                        <small class="text-danger d-none" id="issued_by-error"></small>
                    </div>

                    <input type="hidden" name="issued_by_official_id" id="issuedByOfficialId" value="">

                    {{-- Signature Selection (optional) --}}
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px;color:#1e293b;">
                            <i class="bi bi-signature"></i> Signature (Optional)
                        </label>
                        <select name="signature_id" class="form-select" id="signatureSelect" style="border-radius:8px;font-size:13px;">
                            <option value="">-- Select Signature (optional) --</option>
                            @isset($signatures)
                                @foreach($signatures as $sig)
                                    @php
                                        $off = $sig->official?->resident;
                                        $label = $sig->label ? $sig->label . ' - ' : '';
                                        $display = $label . ($off ? trim($off->first_name . ' ' . $off->last_name) : 'Official');
                                    @endphp
                                    <option value="{{ $sig->id }}" data-official-id="{{ $sig->official_id }}">{{ $display }}</option>
                                @endforeach
                            @endisset
                        </select>
                        <small class="text-danger d-none" id="signature_id-error"></small>
                    </div>

                    {{-- Status (Edit Only) --}}
                    <div class="mb-3 d-none" id="statusGroup">
                        <label class="form-label fw-600" style="font-size:13px;color:#1e293b;">
                            <i class="bi bi-info-circle"></i> Status
                        </label>
                        <select name="status" class="form-select" style="border-radius:8px;font-size:13px;">
                            <option value="Issued">Issued</option>
                            <option value="Revoked">Revoked</option>
                            <option value="Expired">Expired</option>
                        </select>
                        <small class="text-danger d-none" id="status-error"></small>
                    </div>

                    {{-- Loading Indicator --}}
                    <div id="formLoading" class="d-none">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2" style="font-size:13px;">Processing...</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitFormBtn">
                    <i class="bi bi-check-circle"></i> <span id="submitBtnText">Issue Document</span>
                </button>
            </div>
        </div>
    </div>
</div>
