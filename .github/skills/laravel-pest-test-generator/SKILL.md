---
name: laravel-pest-test-generator
description: "Generate Pest framework feature and unit tests matching barangay system patterns. Create tests for authorization, controller actions, model relationships, validation rules, and PDF exports. Use when: writing feature tests, unit tests, testing authorization gates, adding tests for new resources."
---

# Laravel Pest Test Generator Skill

Automates generation of Pest BDD tests following Aegean Barangay System patterns.

## When to Use

- Writing feature tests for controllers
- Testing authorization gates
- Testing model relationships and scopes
- Validating form requests
- Testing PDF export functionality
- Creating unit tests for business logic

## What This Skill Generates

✅ Feature tests with `RefreshDatabase` for HTTP testing  
✅ Authorization gate tests (`$this->authorize()`)  
✅ Factory-based test data with proper relationships  
✅ JSON response assertions  
✅ Blade view assertions  
✅ Database query assertions  
✅ Unit tests for model methods  
✅ Test organization following Pest patterns  

## Example Usage

**Example 1: Feature Test for Resource CRUD**

Ask:
> Generate feature tests for the CommitteeController. Test that only authorized users can view, create, update, and delete committees. Include tests for successful operations (201, 200, 302 redirects), unauthorized access (403), and invalid data (422). Use factories for test data and assert database changes.

Generated test file:
```php
<?php

use App\Models\Committee;
use App\Models\User;

describe('CommitteeController', function () {
    describe('authorization', function () {
        test('unauthorized user cannot view committees', function () {
            $user = User::factory()->create(['role_id' => null]); // Regular user

            $this->actingAs($user)
                ->get(route('committees.index'))
                ->assertForbidden();
        });

        test('authorized user can view committees', function () {
            $user = User::factory()->create(['role_id' => 1]); // Admin
            Committee::factory(5)->create();

            $this->actingAs($user)
                ->get(route('committees.index'))
                ->assertOk()
                ->assertViewHas('committees');
        });
    });

    describe('create', function () {
        test('user can view create form', function () {
            $user = User::factory()->create(['role_id' => 1]);

            $this->actingAs($user)
                ->get(route('committees.create'))
                ->assertOk()
                ->assertViewIs('committees.create');
        });

        test('user can create committee', function () {
            $user = User::factory()->create(['role_id' => 1]);
            $data = [
                'name' => 'Health Committee',
                'description' => 'Committee for health affairs',
                'head_official_id' => null, // Provide valid ID
            ];

            $this->actingAs($user)
                ->post(route('committees.store'), $data)
                ->assertRedirect(route('committees.show', Committee::latest()->first()));

            assertDatabaseHas('committees', [
                'name' => 'Health Committee',
            ]);
        });

        test('validation fails with invalid data', function () {
            $user = User::factory()->create(['role_id' => 1]);

            $this->actingAs($user)
                ->post(route('committees.store'), [
                    'name' => '', // Required
                    'description' => '',
                ])
                ->assertUnprocessable()
                ->assertInvalid(['name', 'description']);
        });
    });

    describe('update', function () {
        test('user can update committee', function () {
            $user = User::factory()->create(['role_id' => 1]);
            $committee = Committee::factory()->create();

            $this->actingAs($user)
                ->put(route('committees.update', $committee), [
                    'name' => 'Updated Committee',
                    'description' => 'Updated description',
                ])
                ->assertRedirect();

            expect($committee->fresh()->name)->toBe('Updated Committee');
        });
    });

    describe('delete', function () {
        test('user can delete committee', function () {
            $user = User::factory()->create(['role_id' => 1]);
            $committee = Committee::factory()->create();

            $this->actingAs($user)
                ->delete(route('committees.destroy', $committee))
                ->assertRedirect();

            expect(Committee::find($committee->id))->toBeNull(); // Soft delete
        });
    });

    describe('json responses', function () {
        test('api request returns json', function () {
            $user = User::factory()->create(['role_id' => 1]);
            $committee = Committee::factory()->create();

            $this->actingAs($user)
                ->getJson(route('committees.show', $committee))
                ->assertOk()
                ->assertJsonStructure(['id', 'name', 'description']);
        });
    });
});
```

**Example 2: Model Unit Tests**

Ask:
> Generate unit tests for the Resident model. Test: full_name property, relationship loading with Household and User, hasBusinesses scope, soft deletes, is_voter property, and relationship queries don't cause N+1 issues.

Generated test file:
```php
<?php

use App\Models\Resident;
use App\Models\Household;
use App\Models\User;

describe('Resident Model', function () {
    describe('properties', function () {
        test('full_name returns first and last name', function () {
            $resident = Resident::make([
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
            ]);

            expect($resident->full_name)->toBe('Juan Dela Cruz');
        });

        test('is_voter property checks voter status', function () {
            $resident = Resident::make(['voter_status' => 'registered']);
            expect($resident->is_voter)->toBeTrue();

            $resident = Resident::make(['voter_status' => 'unregistered']);
            expect($resident->is_voter)->toBeFalse();
        });
    });

    describe('relationships', function () {
        test('resident belongs to household', function () {
            $household = Household::factory()->create();
            $resident = Resident::factory()->create(['household_id' => $household->id]);

            expect($resident->household->id)->toBe($household->id);
        });

        test('resident has user account', function () {
            $user = User::factory()->create();
            $resident = Resident::factory()->create(['user_id' => $user->id]);

            expect($resident->user->id)->toBe($user->id);
        });

        test('resident can have businesses', function () {
            $resident = Resident::factory()->create();
            $businesses = Business::factory(3)->create();
            $resident->businessOwner?->businesses()->sync($businesses);

            expect($resident->businessOwner->businesses)->toHaveCount(3);
        });
    });

    describe('soft deletes', function () {
        test('resident is soft deleted', function () {
            $resident = Resident::factory()->create();
            $resident->delete();

            expect(Resident::find($resident->id))->toBeNull();
            expect(Resident::withTrashed()->find($resident->id))->not->toBeNull();
        });

        test('resident can be restored', function () {
            $resident = Resident::factory()->create();
            $resident->delete();
            $resident->restore();

            expect(Resident::find($resident->id))->not->toBeNull();
        });
    });

    describe('scopes', function () {
        test('voter scope filters registered voters', function () {
            Resident::factory(2)->create(['voter_status' => 'registered']);
            Resident::factory(3)->create(['voter_status' => 'unregistered']);

            expect(Resident::registered()->count())->toBe(2);
        });

        test('active scope filters active residents', function () {
            Resident::factory(2)->create(['residency_status' => 'active']);
            Resident::factory(1)->create(['residency_status' => 'inactive']);

            expect(Resident::active()->count())->toBe(2);
        });
    });

    describe('n+1 prevention', function () {
        test('loading relationships does not cause n+1', function () {
            $residents = Resident::factory(10)->create();

            assertQueryCount(fn () => {
                Resident::with('household', 'user')->get();
            }, equals: 2); // Only 1 query (residents) + 1 for relationships
        });
    });
});
```

**Example 3: Authorization Gate Tests**

Ask:
> Generate tests for authorization gates. Test that residents.view gate only allows admin, captain, secretary, and tanod roles. Test residents.export gate only allows admin, captain, and secretary.

Generated test file:
```php
<?php

use App\Models\User;

describe('Authorization Gates', function () {
    describe('residents.view', function () {
        test('admin can view residents', function () {
            $user = User::factory()->create(['role_id' => Role::firstWhere('name', 'admin')->id]);

            expect($user->can('residents.view'))->toBeTrue();
        });

        test('captain can view residents', function () {
            $user = User::factory()->create(['role_id' => Role::firstWhere('name', 'captain')->id]);

            expect($user->can('residents.view'))->toBeTrue();
        });

        test('regular user cannot view residents', function () {
            $user = User::factory()->create(['role_id' => Role::firstWhere('name', 'resident')->id]);

            expect($user->can('residents.view'))->toBeFalse();
        });
    });

    describe('residents.export', function () {
        test('only admin, captain, secretary can export', function () {
            $admin = User::factory()->create(['role_id' => Role::firstWhere('name', 'admin')->id]);
            $tanod = User::factory()->create(['role_id' => Role::firstWhere('name', 'tanod')->id]);

            expect($admin->can('residents.export'))->toBeTrue();
            expect($tanod->can('residents.export'))->toBeFalse();
        });
    });
});
```

## Key Patterns Applied

### Feature Tests with RefreshDatabase
```php
describe('CommitteeController', function () {
    // Each test gets fresh database
});
```

### Factory-Based Test Data
```php
$user = User::factory()->create(['role_id' => 1]);
$committee = Committee::factory()->create();
```

### Authorization Testing
```php
$this->actingAs($user)
    ->get(route('committees.index'))
    ->assertForbidden(); // or assertOk()
```

### Response Assertions
```php
->assertOk()              // 200
->assertCreated()         // 201
->assertRedirect()        // 302
->assertUnprocessable()   // 422
->assertForbidden()       // 403
```

### Database Assertions
```php
assertDatabaseHas('committees', ['name' => 'Health']);
expect($committee->fresh()->name)->toBe('Updated');
```

## Customization Points

Provide the skill with:
1. **Model/Controller name** (e.g., Committee, CommitteeController)
2. **Test type** (feature or unit)
3. **Scenarios to test** (CRUD, validation, relationships, scopes)
4. **Authorization gates** to test
5. **Edge cases** (null values, invalid enums, etc.)
6. **Database assertions** needed
7. **Soft delete behavior** to verify

## Test File Location

New test files go in:
```
tests/Feature/CommitteeControllerTest.php
tests/Unit/CommitteeTest.php
```

## Running Tests

```bash
composer test                      # All tests
php artisan test tests/Feature     # Feature tests only
php artisan test tests/Unit        # Unit tests only
php artisan test --parallel        # Parallel execution
php artisan test tests/Feature/CommitteeControllerTest.php # Single file
```

## Pest Syntax Reference

```php
// Basic test
test('description', function () {
    expect($value)->toBe($expected);
});

// Grouped tests
describe('group name', function () {
    test('test 1', ...);
    test('test 2', ...);
});

// Before/After hooks
beforeEach(function () {
    // Runs before each test
});

// Mocking
$mock = mock(SomeClass::class);

// Factories
$model = Model::factory()->create();
$models = Model::factory(5)->create();
```

## Common Assertions

```php
expect($value)->toBe($expected);
expect($value)->toEqual($expected);
expect(Collection)->toHaveCount(5);
expect($value)->toBeNull();
expect($value)->toBeTrue();
expect($value)->toThrow(Exception::class);
```

## Next Steps After Generation

1. Review generated tests
2. Run tests: `php artisan test`
3. Add any missing test scenarios
4. Ensure all routes and gates exist
5. Update factories if needed for test data

---

**Related:** See AGENTS.md for testing patterns, authorization gates, and factory conventions.
