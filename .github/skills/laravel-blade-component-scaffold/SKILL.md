---
name: laravel-blade-component-scaffold
description: "Generate reusable Blade components with Alpine.js interactivity, Tailwind CSS styling, and accessibility features. Create form inputs, modals, alerts, cards, data display components. Use when: building UI components, standardizing form inputs, creating reusable card/modal components, adding interactive components."
---

# Laravel Blade Component Scaffold Skill

Automates generation of reusable Blade components following Aegean Barangay System UI patterns.

## When to Use

- Building standardized form inputs (text, select, checkbox, etc.)
- Creating reusable layout components (cards, alerts, modals)
- Adding interactive components with Alpine.js
- Standardizing UI across views
- Reducing template duplication
- Implementing accessibility features

## What This Skill Generates

✅ Blade component classes in `app/View/Components/`  
✅ Corresponding Blade templates in `resources/views/components/`  
✅ Tailwind CSS styling (dark mode compatible)  
✅ Alpine.js interactivity (modals, dropdowns, toggles)  
✅ Accessibility attributes (ARIA labels, focus management)  
✅ Props documentation via inline comments  
✅ Slot support for flexible content  
✅ Error handling and form state management  

## Example Usage

**Example 1: Form Input Component**

Ask:
> Generate a reusable form input component named "FormInput". It should support: label, name, type (text/email/number/password), placeholder, error messages, required indicator, helper text, disabled state. Style with Tailwind and include accessibility.

Generated component class:
```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormInput extends Component
{
    /**
     * Create a new component instance.
     *
     * @param string $label - Display label
     * @param string $name - Input name and id
     * @param string $type - Input type (text, email, number, password, etc.)
     * @param string|null $placeholder - Placeholder text
     * @param string|null $value - Current value
     * @param bool $required - Show required indicator
     * @param string|null $error - Error message
     * @param string|null $helper - Helper text below input
     * @param bool $disabled - Disable input
     * @param array $attributes - Additional HTML attributes
     */
    public function __construct(
        public string $label = '',
        public string $name = '',
        public string $type = 'text',
        public ?string $placeholder = null,
        public ?string $value = null,
        public bool $required = false,
        public ?string $error = null,
        public ?string $helper = null,
        public bool $disabled = false,
        public array $attributes = [],
    ) {}

    public function render()
    {
        return view('components.form-input');
    }
}
```

Generated component view:
```blade
<div class="mb-4">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if ($required)
                <span class="text-red-500 ml-1" aria-label="required">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @disabled($disabled)
        {{ $attributes->merge([
            'class' => 'w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ' .
                       ($error ? 'border-red-500 bg-red-50' : 'border-gray-300')
        ]) }}
        aria-describedby="{{ $error ? $name . '-error' : ($helper ? $name . '-helper' : '') }}"
        @required($required)
    />

    @if ($error)
        <p id="{{ $name }}-error" class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif

    @if ($helper)
        <p id="{{ $name }}-helper" class="mt-1 text-sm text-gray-500">{{ $helper }}</p>
    @endif
</div>
```

Usage in views:
```blade
<x-form-input
    label="Full Name"
    name="full_name"
    type="text"
    placeholder="Enter your full name"
    :required="true"
    :error="$errors->first('full_name')"
    helper="First and last name"
/>
```

**Example 2: Modal Component with Alpine.js**

Ask:
> Generate a reusable Modal component. It should: accept title and description, have slots for header/body/footer, support Alpine.js x-show for toggle, include close button, backdrop click dismissal, keyboard escape handling, focus trap for accessibility.

Generated component class:
```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    /**
     * Create a new component instance.
     *
     * @param string $id - Unique modal identifier
     * @param string|null $title - Modal title
     * @param bool $dismissible - Allow closing via backdrop/escape
     */
    public function __construct(
        public string $id = 'modal',
        public ?string $title = null,
        public bool $dismissible = true,
    ) {}

    public function render()
    {
        return view('components.modal');
    }
}
```

Generated component view:
```blade
<div
    x-data="{ open: @entangle('open') }"
    @keydown.escape.window="dismissible && (open = false)"
    class="relative z-50"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        @click="dismissible && (open = false)"
        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <!-- Modal -->
    <div
        x-show="open"
        @click.stop
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b">
                @if ($title)
                    <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
                @else
                    {{ $header ?? '' }}
                @endif

                @if ($dismissible)
                    <button
                        @click="open = false"
                        class="text-gray-400 hover:text-gray-600 transition"
                        aria-label="Close modal"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Body -->
            <div class="p-6">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if (isset($footer))
                <div class="flex items-center justify-end gap-3 p-6 border-t bg-gray-50">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
```

Usage in views:
```blade
<x-modal id="deleteModal" title="Confirm Delete" :dismissible="true">
    <p>Are you sure you want to delete this item?</p>

    <x-slot name="footer">
        <button @click="open = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100">
            Cancel
        </button>
        <form method="POST" action="{{ route('items.destroy', $item) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700">
                Delete
            </button>
        </form>
    </x-slot>
</x-modal>
```

**Example 3: Card Component**

Ask:
> Generate a reusable Card component. It should: support header with optional action buttons, body content slot, footer section, hover effects, shadow, flexible styling with Tailwind.

Generated component view:
```blade
<div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
    @if (isset($header) || $title ?? false)
        <div class="px-6 py-4 border-b flex items-center justify-between">
            @if ($title ?? false)
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            @else
                {{ $header }}
            @endif

            @if (isset($actions))
                <div class="flex gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="px-6 py-4">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="px-6 py-4 border-t bg-gray-50">
            {{ $footer }}
        </div>
    @endif
</div>
```

Usage:
```blade
<x-card title="Resident Information">
    <dl class="grid grid-cols-2 gap-4">
        <div>
            <dt class="text-sm font-medium text-gray-500">Full Name</dt>
            <dd class="text-lg font-semibold text-gray-900">{{ $resident->full_name }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Contact</dt>
            <dd class="text-lg text-gray-900">{{ $resident->contact_number }}</dd>
        </div>
    </dl>

    <x-slot name="footer">
        <a href="{{ route('residents.edit', $resident) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
    </x-slot>
</x-card>
```

**Example 4: Alert Component**

Ask:
> Generate an Alert component. Support types: success (green), error (red), warning (yellow), info (blue). Include icon, title, message, optional action button, dismissible with Alpine.js.

Generated component view:
```blade
<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="ease-in duration-200"
    class="p-4 rounded-lg flex items-start gap-3 {{ $colorClasses }}"
    role="alert"
>
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        @if ($type === 'success')
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        @elseif ($type === 'error')
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        @endif
    </svg>

    <div class="flex-1">
        @if ($title ?? false)
            <h3 class="font-semibold">{{ $title }}</h3>
        @endif
        <p class="text-sm">{{ $slot }}</p>
    </div>

    @if (isset($action))
        <div class="flex-shrink-0">
            {{ $action }}
        </div>
    @endif

    <button
        @click="show = false"
        class="flex-shrink-0 opacity-75 hover:opacity-100"
        aria-label="Close"
    >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
    </button>
</div>
```

Usage:
```blade
<x-alert type="success" title="Success!">
    Resident has been created successfully.
</x-alert>

<x-alert type="error" title="Error">
    {{ $errors->first() }}
</x-alert>
```

## Component File Structure

Components are organized in two locations:

**Component Classes** (optional, for logic):
```
app/View/Components/
  ├── FormInput.php
  ├── Modal.php
  ├── Card.php
  └── Alert.php
```

**Component Views**:
```
resources/views/components/
  ├── form-input.blade.php
  ├── modal.blade.php
  ├── card.blade.php
  └── alert.blade.php
```

## Component Usage Conventions

```blade
<!-- Class-based component with props -->
<x-form-input name="email" label="Email" type="email" />

<!-- Anonymous component (template only) -->
<x-alert type="success">Success message</x-alert>

<!-- With slots -->
<x-modal title="Title">
    <p>Content here</p>
    <x-slot name="footer">Footer content</x-slot>
</x-modal>

<!-- Dynamic attributes -->
<x-form-input name="name" :attributes="$attributes->class('w-full')" />
```

## Tailwind & Alpine Integration

### Tailwind Classes Used
```
Spacing: px-*, py-*, mb-*, gap-*
Colors: text-gray-*, bg-gray-*, border-gray-*
Typography: font-semibold, text-sm, text-lg
Interactive: hover:*, focus:*, disabled:*
Responsive: sm:*, md:*, lg:*
```

### Alpine.js Directives
```
x-data - Initialize component state
x-show - Toggle display with CSS
x-on - Event listeners (@click, @keydown)
x-transition - Animation transitions
x-entangle - Two-way binding
@focus - Focus event
@click.stop - Stop event propagation
```

## Accessibility Features

- ARIA labels on buttons (`aria-label`)
- ARIA describedby for form helpers (`aria-describedby`)
- Role attributes (`role="alert"`, `role="dialog"`)
- Focus management in modals
- Semantic HTML (`<label>`, `<button>`, `<form>`)
- Keyboard navigation (Escape key, Tab order)

## Customization Points

Provide the skill with:
1. **Component name** (FormInput, Modal, Card, etc.)
2. **Props** (parameters the component needs)
3. **Slots** (flexible content areas)
4. **Styling** (Tailwind classes, color variants)
5. **Interactivity** (Alpine.js features)
6. **Accessibility** requirements
7. **Examples** of typical usage

## Running Component Generation

```bash
# Generate component class only
php artisan make:component FormInput

# Generate view-only (anonymous) component
php artisan make:component Alert --view

# Our skill generates both with patterns
```

## Next Steps After Generation

1. Add component to `app/View/Components/` (if class-based)
2. Add view to `resources/views/components/`
3. Use component in views with `<x-component-name />`
4. Test interactivity in browser
5. Adjust Tailwind/Alpine as needed

---

**Related:** See AGENTS.md for frontend stack (Blade, Tailwind, Alpine.js), component patterns, and UI conventions.
