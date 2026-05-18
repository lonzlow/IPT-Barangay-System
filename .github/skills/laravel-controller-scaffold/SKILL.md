---
name: laravel-controller-scaffold
description: "Generate RESTful Laravel controllers with proper request validation, authorization gates, relationship eager loading, and dual JSON/redirect response formats. Use when: scaffolding new resource controllers, need boilerplate for CRUD operations, integrating new features that follow barangay system patterns."
---

# Laravel Controller Scaffold Skill

Automates generation of RESTful controllers following the Aegean Barangay System patterns.

## When to Use

- Creating new resource controllers (residents, officials, committees, etc.)
- Need boilerplate for standard CRUD operations
- Integrating authorization gates and relationship loading
- Adding PDF export or DataTables endpoints

## What This Skill Generates

✅ Full RESTful controller class with all 7 standard methods  
✅ Request validation with `Rule::enum()` for enums  
✅ Gate-based authorization checks (`$this->authorize()`)  
✅ Eager loading of relationships (`with()`)  
✅ Dual response format (JSON for API, redirect for web)  
✅ Index method with DataTables support stub  
✅ Store/Update methods with proper error handling  
✅ PDF export method stub  

## Example Usage

Ask:
> Generate a controller for the CommitteeAccomplishment resource. It should have index, create, store, show, edit, update, destroy methods. Include authorization for committee.create, committee.edit, committee.delete gates. Load accomplishment, committee, and official relationships.

The skill will create:
```php
<?php

namespace App\Http\Controllers;

use App\Models\CommitteeAccomplishment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommitteeAccomplishmentController extends Controller
{
    public function index()
    {
        $accomplishments = CommitteeAccomplishment::with('committee', 'official')
            ->paginate(15);

        if (request()->expectsJson()) {
            return response()->json($accomplishments);
        }

        return view('committee-accomplishments.index', compact('accomplishments'));
    }

    public function create()
    {
        $this->authorize('committee.create');
        return view('committee-accomplishments.create');
    }

    public function store(Request $request)
    {
        $this->authorize('committee.create');

        $validated = $request->validate([
            'committee_id' => ['required', 'exists:committees,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'completion_date' => ['required', 'date'],
        ]);

        $accomplishment = CommitteeAccomplishment::create($validated);

        if (request()->expectsJson()) {
            return response()->json($accomplishment, 201);
        }

        return redirect()->route('committee-accomplishments.show', $accomplishment)
            ->with('success', 'Accomplishment created successfully.');
    }

    public function show(CommitteeAccomplishment $accomplishment)
    {
        $accomplishment->load('committee', 'official');

        if (request()->expectsJson()) {
            return response()->json($accomplishment);
        }

        return view('committee-accomplishments.show', compact('accomplishment'));
    }

    public function edit(CommitteeAccomplishment $accomplishment)
    {
        $this->authorize('committee.edit');
        $accomplishment->load('committee', 'official');
        return view('committee-accomplishments.edit', compact('accomplishment'));
    }

    public function update(Request $request, CommitteeAccomplishment $accomplishment)
    {
        $this->authorize('committee.edit');

        $validated = $request->validate([
            'committee_id' => ['required', 'exists:committees,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'completion_date' => ['required', 'date'],
        ]);

        $accomplishment->update($validated);

        if (request()->expectsJson()) {
            return response()->json($accomplishment);
        }

        return redirect()->route('committee-accomplishments.show', $accomplishment)
            ->with('success', 'Accomplishment updated successfully.');
    }

    public function destroy(CommitteeAccomplishment $accomplishment)
    {
        $this->authorize('committee.delete');
        $accomplishment->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('committee-accomplishments.index')
            ->with('success', 'Accomplishment deleted successfully.');
    }

    // DataTables endpoint (optional)
    public function data(Request $request)
    {
        return DataTables::of(CommitteeAccomplishment::with('committee', 'official'))
            ->addColumn('action', function ($row) {
                return view('partials.actions', ['model' => $row]);
            })
            ->toJson();
    }
}
```

## Key Patterns Applied

### Authorization
Uses Gate-based authorization defined in `AppServiceProvider`:
```php
$this->authorize('resource.action');
```

### Relationship Loading
Always eager-loads relationships to prevent N+1 queries:
```php
CommitteeAccomplishment::with('committee', 'official')->paginate();
```

### Response Format
Checks `expectsJson()` for dual response:
```php
if (request()->expectsJson()) {
    return response()->json($data);
}
return redirect()->route('...')->with('success', 'Message');
```

### Validation
Uses `Rule::enum()` for enum fields:
```php
'status' => ['required', Rule::enum(CommitteeStatus::class)]
```

## Customization Points

Provide the skill with:
1. **Model name** (e.g., CommitteeAccomplishment)
2. **Authorization gates** (e.g., committee.create, committee.edit)
3. **Relationships to load** (e.g., committee, official)
4. **Validation rules** (field name → validation array)
5. **Include DataTables?** (yes/no for data() method)
6. **Include PDF export?** (yes/no for exportPdf() method)

## Next Steps After Generation

1. Create corresponding views in `resources/views/{resource}/`
2. Register route in `routes/web.php`:
   ```php
   Route::resource('committee-accomplishments', CommitteeAccomplishmentController::class)
       ->middleware('auth');
   ```
3. Add tests in `tests/Feature/CommitteeAccomplishmentTest.php`
4. Define Gates in `AppServiceProvider` if not already present

---

**Related:** See AGENTS.md for controller patterns, authorization gates, and RESTful conventions.
