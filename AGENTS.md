# Aegean Barangay System - AI Agent Guide

This is a comprehensive Laravel 12 application for managing barangay (village) administrative operations in the Philippines.

---

## Quick Start for Agents

### Setup Commands
```bash
composer setup              # Full bootstrap: install, key:generate, migrate, npm install, build
composer dev               # Run: artisan serve + queue:listen + npm run dev (concurrent)
composer test              # Run Pest test suite
php artisan migrate        # Run pending migrations
npm run build              # Production build
npm run dev               # Vite dev server with hot reload
```

### Essential Routes
- **Authentication:** `routes/auth.php` (Laravel Breeze scaffolding)
- **Web Resources:** `routes/web.php` (protected by `auth` middleware)
- **Controllers:** `app/Http/Controllers/` (RESTful pattern)
- **Models:** `app/Models/` (UUID PKs, SoftDeletes, relationships)
- **Tests:** `tests/Feature/` and `tests/Unit/` (Pest framework)

---

## Core Domain & Data Model

### Primary Models & Relationships
```
Resident ──────────┬─→ Household ─→ Purok
                   ├─→ User (auth)
                   ├─→ Official ─→ Committee
                   └─→ BusinessOwner ─→ Business

Committee ────→ CommitteeAttendance, CommitteeRecord, CommitteeMedia, CommitteeActivity
                ├─→ CommitteeAccomplishment
                └─→ OfficialAssignment

Document ──────→ DocumentTemplate (PDF generation)
Blotter ────────→ Incident/complaint tracking
ActivityLog ────→ Audit trail for all actions
```

**Key Traits in Models:**
- `HasUuids` – UUID primary keys (not auto-increment)
- `SoftDeletes` – Soft delete support for audit trail
- Relationships use eager loading (`with()`) to prevent N+1

### Data Patterns
- **Factories:** Located in `database/factories/`, use `fake('fil_PH')` for Philippine locale data
- **Seeders:** Located in `database/seeders/`, use `WithoutModelEvents` trait
- **Migrations:** Chronological order, include pivot tables for many-to-many relations
- **Pivot Tables:** `business_owner_business`, `committee_official` (via OfficialAssignment)

---

## Authorization & Access Control

### Gate-Based Authorization
All authorization logic is defined in `app/Providers/AppServiceProvider.php` using Laravel Gates (not Policies).

**Key Roles:**
- Admin, Punong Barangay, Barangay Secretary, Treasurer, Kagawad
- SK Chairperson, Tanod, Health Worker/BHW, BDRRM Coordinator, Encoder, Auditor

**Pattern:** Gates check user role and permission, e.g.:
```php
Gate::define('residents.view', fn($user) => $user->hasRole(['admin', 'captain', 'secretary', 'tanod']));
Gate::define('residents.export', fn($user) => $user->hasRole(['admin', 'captain', 'secretary']));
```

**Usage in Controllers:**
```php
$this->authorize('residents.view');  // In controller methods
middleware('can:residents.view')      // In route definitions
```

---

## Controller & Route Conventions

### RESTful Pattern
All controllers follow standard Laravel resource conventions with extensions:
```php
// Controllers inherit from base Controller
class ResidentController extends Controller
{
    public function index()     // GET /residents → List view + DataTables support
    public function create()    // GET /residents/create → Form view
    public function store()     // POST /residents → Validate, save, redirect or JSON
    public function show()      // GET /residents/{id} → Detail view
    public function edit()      // GET /residents/{id}/edit → Edit form
    public function update()    // PUT /residents/{id} → Validate, update, redirect
    public function destroy()   // DELETE /residents/{id} → Delete, redirect
}
```

### Request Validation
- Use inline `Request::validate()` with `Rule` class for enums/custom logic
- Example: `'gender' => ['required', Rule::enum(Gender::class)]`

### Response Format
Controllers check `expectsJson()` to determine response type:
```php
if (request()->expectsJson()) {
    return response()->json($data, 201);
} else {
    return redirect()->route('residents.show', $resident);
}
```

### Route Registration
```php
// web.php
Route::resource('residents', ResidentController::class)->middleware('auth');
Route::get('/residents/data', [ResidentController::class, 'data']); // DataTables endpoint
Route::post('/residents/{resident}/export-pdf', [ResidentController::class, 'exportPdf']);
```

### Eager Loading
Always use `with()` to load relationships:
```php
$residents = Resident::with('household', 'user', 'businessOwner')->paginate();
```

---

## Testing with Pest

### Framework: Pest v4.4
- Located in `tests/` directory: `Feature/` (integration) and `Unit/` (isolated)
- Configuration in `tests/Pest.php`: `RefreshDatabase` middleware applies to Feature tests
- Database: SQLite in-memory (`:memory:`) for speed
- PHPUnit config: `phpunit.xml` (standard setup)

### Test Pattern
```php
// Feature test - use RefreshDatabase, factories, HTTP calls
test('user can view residents', function () {
    $user = User::factory()->create(['role_id' => 1]);
    $resident = Resident::factory()->create();
    
    $this->actingAs($user)
        ->get('/residents')
        ->assertStatus(200)
        ->assertSee($resident->name);
});

// Unit test - isolated business logic
test('resident name is formatted correctly', function () {
    $resident = Resident::make(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);
    expect($resident->full_name)->toBe('Juan Dela Cruz');
});
```

### Run Tests
```bash
composer test                    # All tests
php artisan test tests/Feature   # Feature tests only
php artisan test tests/Unit      # Unit tests only
php artisan test --parallel      # Parallel execution
```

---

## Frontend Stack & Conventions

### Technology
- **Templating:** Blade (server-side rendering, located in `resources/views/`)
- **CSS:** Tailwind CSS v3.1 + `@tailwindcss/forms` plugin (`resources/css/app.css`)
- **JavaScript:** Alpine.js v3.15 for reactivity + Axios for HTTP (`resources/js/app.js`)
- **Build Tool:** Vite v7 with Laravel Vite plugin
- **Icons:** Bootstrap Icons (`bi-*` classes) for UI icons

### Blade Convention
- Components in `app/View/Components/`
- Use `@component` or `<x-component-name />` syntax
- Pass data via component props

### Alpine.js Pattern
```blade
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

### Vite Integration
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

## Key Dependencies & Libraries

| Package | Version | Purpose |
|---------|---------|---------|
| **laravel/framework** | ^12.0 | Core framework |
| **barryvdh/laravel-dompdf** | ^3.1 | PDF generation (residents, documents) |
| **yajra/laravel-datatables-oracle** | ^12.7 | Server-side DataTables rendering |
| **laravel/breeze** | ^2.4 | Auth scaffolding |
| **pestphp/pest** | ^4.4 | BDD testing framework |
| **tailwindcss** | ^3.1 | CSS framework |
| **alpinejs** | ^3.15 | Reactive JS framework |
| **vite** | ^7 | Build tool |

---

## Common Development Patterns

### DataTables Server-Side Rendering
```php
// Controller
public function data(Request $request)
{
    return DataTables::of(Resident::query())
        ->addColumn('action', fn($row) => view('partials.actions', ['model' => $row]))
        ->toJson();
}
```

```blade
<!-- View -->
<table id="residents-table" class="table table-striped">
    <thead><tr><th>Name</th><th>Address</th><th>Action</th></tr></thead>
</table>

<script>
    $('#residents-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("residents.data") }}',
    });
</script>
```

### PDF Export
```php
// Controller
public function exportPdf(Resident $resident)
{
    $pdf = PDF::loadView('residents.pdf', ['resident' => $resident]);
    return $pdf->download("resident_{$resident->id}.pdf");
}
```

### Flash Messages & Session
```php
// Controller
return redirect()->route('residents.show', $resident)
    ->with('success', 'Resident created successfully.');

// Blade
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
```

### Soft Deletes
```php
$resident = Resident::find(1);
$resident->delete();              // Soft delete (deleted_at set)
$resident->restore();             // Restore
$resident->forceDelete();         // Permanent delete
Resident::withTrashed()->get();  // Include soft-deleted
Resident::onlyTrashed()->get();  // Only soft-deleted
```

---

## File Structure Conventions

```
app/
  ├── Http/
  │   ├── Controllers/          # Resource controllers (RESTful)
  │   └── Requests/             # Form request validation (if needed)
  ├── Models/                   # Eloquent models (UUID PKs, SoftDeletes)
  ├── Providers/
  │   ├── AppServiceProvider    # Gates/authorization logic
  │   └── EventServiceProvider  # Event listeners
  ├── Listeners/                # Event handlers (login/logout)
  ├── Policies/                 # Authorization policies (minimal use)
  └── View/Components/          # Blade components

database/
  ├── factories/                # Model factories for testing
  ├── migrations/               # Schema definitions
  └── seeders/                  # Data seeders

resources/
  ├── css/                      # Tailwind CSS (app.css)
  ├── js/                       # Alpine.js, Axios (app.js)
  └── views/
      ├── layouts/              # Master layout (app.blade.php)
      ├── auth/                 # Auth views (Breeze)
      ├── residents/            # Resident views (index, create, edit, etc.)
      ├── officials/            # Official management
      ├── committees/           # Committee management
      └── partials/             # Reusable components

routes/
  ├── web.php                   # Protected web routes
  └── auth.php                  # Authentication routes (Breeze)

tests/
  ├── Feature/                  # Integration tests (with DB)
  └── Unit/                     # Unit tests (isolated)

config/
  ├── app.php                   # App configuration
  ├── auth.php                  # Auth configuration
  ├── database.php              # Database configuration
  └── filesystems.php           # Storage configuration
```

---

## When Working on This Project

### Before Making Changes
1. **Understand the domain:** Review the model relationships and Gates in AppServiceProvider
2. **Check authorization:** Verify the current user has permission via Gates
3. **Load relationships:** Use `with()` to avoid N+1 queries
4. **Use factories in tests:** Leverage existing factories in `database/factories/`
5. **Follow naming:** Use consistent blade view names (`index`, `create`, `edit`, etc.)

### When Creating New Features
1. **Create model** with UUID trait and relationships
2. **Create migration** with foreign keys and constraints
3. **Create factory** for testing
4. **Create controller** with RESTful methods
5. **Create routes** in `web.php` with resource routing
6. **Create views** in `resources/views/{resource}/`
7. **Add tests** in `tests/Feature/` (Pest syntax)
8. **Define Gates** in `AppServiceProvider` for authorization
9. **Run `npm run dev`** to hot-reload frontend changes

### Common Issues & Solutions
- **N+1 queries:** Always use `with()` for relationships
- **Soft delete issues:** Use `withTrashed()` if querying deleted records
- **Auth failures:** Check Gates in AppServiceProvider, not route middleware alone
- **Hot reload not working:** Restart `npm run dev` or ensure Vite is running
- **Migration conflicts:** Check migration order; use chronological timestamps
- **Test failures:** Ensure factories use correct relationships and factory methods

---

## Useful Resources
- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Pest Testing](https://pestphp.com/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Alpine.js](https://alpinejs.dev/)
- [Laravel Breeze](https://laravel.com/docs/12.x/starter-kits#laravel-breeze)
