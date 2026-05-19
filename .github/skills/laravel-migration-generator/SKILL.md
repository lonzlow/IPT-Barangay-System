---
name: laravel-migration-generator
description: "Generate Laravel migrations with UUID primary keys, SoftDeletes columns, foreign key constraints, indexes, and proper table relationships. Use when: creating new tables, adding columns to existing tables, setting up many-to-many pivot tables, need migration following barangay system patterns."
---

# Laravel Migration Generator Skill

Automates generation of Laravel migrations following Aegean Barangay System patterns (UUID PKs, SoftDeletes, foreign constraints).

## When to Use

- Creating new database tables
- Adding/modifying columns in existing tables
- Setting up many-to-many pivot tables
- Need to follow UUID + SoftDeletes convention
- Adding foreign key relationships

## What This Skill Generates

✅ UUID primary key with `id()` shortcut  
✅ SoftDeletes columns (`deleted_at`) for audit trail  
✅ Foreign key relationships with `foreignId()->constrained()`  
✅ Unique and indexed columns for performance  
✅ Nullable/default column modifiers  
✅ Proper table naming conventions (snake_case, plural)  
✅ Pivot table setup for many-to-many  
✅ Chronological migration timestamp  

## Example Usage

**Example 1: New Resource Table**

Ask:
> Generate a migration for a new "BlotterIncidents" table. Include columns: title (string, required), description (text, nullable), incident_type (enum: complaint, dispute, violation, other), severity (enum: low, medium, high, critical), status (enum: open, investigating, resolved, closed), reporter_id (foreign to residents), incident_location (string, required), incident_date (datetime), resolved_date (datetime, nullable). Add SoftDeletes and created_at/updated_at.

Generated migration:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blotter_incidents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('incident_type'); // complaint, dispute, violation, other
            $table->string('severity'); // low, medium, high, critical
            $table->string('status'); // open, investigating, resolved, closed
            $table->foreignUlid('reporter_id')->constrained('residents')->onDelete('cascade');
            $table->string('incident_location');
            $table->dateTime('incident_date');
            $table->dateTime('resolved_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('incident_type');
            $table->index('severity');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blotter_incidents');
    }
};
```

**Example 2: Pivot Table (Many-to-Many)**

Ask:
> Generate a pivot table migration for business_owner-business many-to-many relationship. The table should be named "business_owner_business" with foreign keys to both tables, timestamps, and indexes for performance.

Generated migration:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_owner_business', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('business_owner_id')
                ->constrained('business_owners')
                ->onDelete('cascade');
            $table->foreignUlid('business_id')
                ->constrained('businesses')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['business_owner_id', 'business_id']);
            $table->index('business_owner_id');
            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_owner_business');
    }
};
```

**Example 3: Adding Columns to Existing Table**

Ask:
> Generate a migration to add three columns to the "residents" table: voter_id (string, nullable, unique), voter_status (enum: registered, unregistered, inactive), voter_registration_date (date, nullable).

Generated migration:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->string('voter_id')->nullable()->unique()->after('id');
            $table->string('voter_status')->nullable()->after('voter_id'); // registered, unregistered, inactive
            $table->date('voter_registration_date')->nullable()->after('voter_status');
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn(['voter_id', 'voter_status', 'voter_registration_date']);
        });
    }
};
```

## Key Patterns Applied

### UUID Primary Keys
Uses `ulid()` for UUID-like identifiers (sortable, performant):
```php
$table->ulid('id')->primary();
```

### SoftDeletes
Always included for audit trail compliance:
```php
$table->softDeletes(); // adds deleted_at column
```

### Foreign Key Constraints
Uses `foreignUlid()->constrained()` with cascade delete:
```php
$table->foreignUlid('resident_id')->constrained()->onDelete('cascade');
```

### Timestamps
Laravel convention for created_at/updated_at:
```php
$table->timestamps();
```

### Indexes
Adds indexes on frequently queried columns:
```php
$table->index('status');
$table->index('created_at');
$table->unique(['user_id', 'email']);
```

## Customization Points

Provide the skill with:
1. **Table name** (will be pluralized, snake_case)
2. **Columns** with types:
   - String/text/email
   - Integer/bigInteger/smallInteger
   - Enum (list values)
   - Date/DateTime/Time
   - Boolean/JSON
3. **Foreign keys** (references, cascade behavior)
4. **Unique constraints** (single or composite)
5. **Indexes** (performance optimization)
6. **Table modification** (adding/dropping columns vs creating table)
7. **SoftDeletes** (yes/no)

## Migration Naming Convention

Files are named with timestamp:
```
database/migrations/2026_05_18_130156_create_blotter_incidents_table.php
database/migrations/2026_05_18_140230_add_voter_status_to_residents_table.php
database/migrations/2026_05_18_150445_create_business_owner_business_table.php
```

Use `php artisan make:migration {name}` or let this skill generate the full path.

## Running Migrations

```bash
php artisan migrate              # Run all pending migrations
php artisan migrate:rollback     # Rollback last batch
php artisan migrate:reset        # Rollback all migrations
php artisan migrate:fresh --seed # Fresh database + seeding
```

## Common Column Modifiers

```php
$table->string('email')->unique();           // Unique constraint
$table->text('description')->nullable();     // Allow NULL
$table->integer('count')->default(0);        // Default value
$table->string('status')->after('id');       // Column position
$table->boolean('is_active')->default(true); // Boolean with default
$table->json('metadata')->nullable();        // JSON storage
$table->enum('role', ['admin', 'user']);     // Enum (MySQL 5.7+)
```

## Next Steps After Generation

1. Review migration and adjust as needed
2. Run: `php artisan migrate`
3. Update corresponding Model class with:
   ```php
   use HasUuids;
   use SoftDeletes;
   
   protected $fillable = ['title', 'description', ...];
   protected $casts = ['incident_date' => 'datetime'];
   ```
4. Create factory for testing: `php artisan make:factory BlotterIncidentFactory`
5. Add relationship methods in related models

---

**Related:** See AGENTS.md for database patterns, model conventions, and relationship setup.
