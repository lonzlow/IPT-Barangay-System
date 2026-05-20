# Barangay Management System Project Checklist

This checklist is based on the current codebase scan of routes, controllers, models, migrations, and tests.

## Status Legend

- `[x]` Implemented
- `[~]` Partially implemented or wired at the core level only
- `[ ]` Not yet implemented

## Accomplished So Far

### 1. Resident Management

- `[x]` Resident CRUD exists in [app/Http/Controllers/ResidentController.php](app/Http/Controllers/ResidentController.php)
- `[x]` Resident search/data endpoint is routed in [routes/web.php](routes/web.php)
- `[x]` Demographics view is implemented
- `[x]` PDF export for resident lists is implemented
- `[x]` Feature coverage exists for document and blotter flows that depend on resident data

### 2. Barangay Document Issuance

- `[x]` Document CRUD exists in [app/Http/Controllers/DocumentController.php](app/Http/Controllers/DocumentController.php)
- `[x]` Certificate template management exists in [app/Http/Controllers/CertificateTemplateController.php](app/Http/Controllers/CertificateTemplateController.php)
- `[x]` Preview, PDF download, and PDF export flows are implemented
- `[x]` Resident-specific document lookup and business lookup endpoints are present
- `[x]` Feature tests cover standard certificates, business clearance behavior, PDF export, and datatable columns

### 3. Blotter Management

- `[x]` Blotter CRUD exists in [app/Http/Controllers/BlotterController.php](app/Http/Controllers/BlotterController.php)
- `[x]` Datatable/data endpoint is routed
- `[x]` Evidence upload is implemented
- `[x]` Export endpoint is implemented
- `[x]` Feature tests cover CRUD flow, evidence upload, and export

### 4. Household and Purok Management

- `[x]` Household CRUD exists in [app/Http/Controllers/HouseholdController.php](app/Http/Controllers/HouseholdController.php)
- `[x]` Purok CRUD exists in [app/Http/Controllers/PurokController.php](app/Http/Controllers/PurokController.php)
- `[x]` Household statistics endpoint is routed
- `[x]` The data model includes household, purok, and resident relationships

### 5. Business Management

- `[x]` Business CRUD exists in [app/Http/Controllers/BusinessController.php](app/Http/Controllers/BusinessController.php)
- `[x]` Business data endpoint is routed
- `[x]` Business-owner relationships are present in the models

### 6. Barangay Officials and Staff Management

- `[x]` Official CRUD exists in [app/Http/Controllers/OfficialController.php](app/Http/Controllers/OfficialController.php)
- `[x]` Official assignments are modeled
- `[x]` Role-based access control is already used for official-facing permissions

### 7. Committee Management

- `[x]` Committee CRUD exists in [app/Http/Controllers/CommitteeController.php](app/Http/Controllers/CommitteeController.php)
- `[x]` Committee records are exposed through [app/Http/Controllers/ReportController.php](app/Http/Controllers/ReportController.php)
- `[x]` Committee-related models already exist for records, media, attendance, activities, and accomplishments

### 8. Reports, Auth, and Access Control

- `[x]` User authentication routes are present through Laravel Breeze scaffolding
- `[x]` Profile management is implemented
- `[x]` User CRUD exists in [app/Http/Controllers/UserController.php](app/Http/Controllers/UserController.php)
- `[x]` Gate-based authorization is defined in [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php)
- `[x]` The project has feature and unit test coverage in place

## Still To Be Done

### 1. Business Permit Workflow

- `[ ]` Add a dedicated business permit controller and routes
- `[ ]` Build create/view/edit flows for permits and renewals
- `[ ]` Add QR code or reference number generation for permits
- `[ ]` Add permit validity tracking and renewal reminders
- `[ ]` Add tests for permit issuance and renewal

### 2. Committee Submodules

- `[ ]` Add dedicated CRUD or upload flows for committee photos, videos, and media archives
- `[ ]` Add activity logs or activity timeline pages per committee
- `[ ]` Add attendance sheet management per committee
- `[ ]` Add accomplishments, partnership records, inventory, certificates, and report modules per committee
- `[ ]` Add committee-specific lists such as BPSO, clinic staff, street sweepers, TODA drivers/operators, and evacuation center records where applicable

### 3. Reports and Analytics

- `[ ]` Add population and demographic dashboards
- `[ ]` Add monthly, quarterly, and annual report generation
- `[ ]` Add Excel export support alongside PDF exports
- `[ ]` Add filters for gender, age group, and voter status summaries

### 4. Backup, Restore, and Audit Enhancements

- `[ ]` Add database backup and restore tooling
- `[ ]` Expand audit logging for critical admin actions if needed
- `[ ]` Verify coverage for account lifecycle and activity log flows

### 5. Optional UI and Data Enhancements

- `[ ]` Add digital ID generation for officials if still required
- `[ ]` Confirm voter tracking fields and family-size reporting are complete
- `[ ]` Confirm resident status tracking covers active, deceased, and transferred states everywhere they are used

## Priority Todo List

1. Finish the business permit workflow, because it is a core administrative feature that is not yet wired end to end.
2. Expand committee management into the committee-specific submodules required by the proposal.
3. Add the reporting and analytics layer, including dashboard summaries and Excel exports.
4. Add backup and restore support, then validate the admin security and audit flow.
5. Fill any remaining optional features such as digital IDs and committee-specific reference lists.

## Notes

- The current codebase already covers the main CRUD and document/blotter flows.
- The largest gaps are the specialized submodules and administrative reporting features requested in the proposal.
- This checklist should be updated again after each major module is completed.