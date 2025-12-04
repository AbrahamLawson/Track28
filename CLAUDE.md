# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

E-commerce competitor analysis tool built with Laravel 12 + Vue.js 3. The application allows users to input a product or store URL and uses OpenAI (GPT-4) to analyze and list competitors with details like name, URL, price, and description.

**Tech Stack:**
- Backend: Laravel 12 (PHP 8.2+)
- Frontend: Vue.js 3 with Composition API
- Database: SQLite (default)
- Build Tool: Vite
- CSS: Tailwind CSS 4
- AI: OpenAI GPT-4 via openai-php/laravel

## Development Commands

### Initial Setup
```bash
composer setup
```
This runs the complete setup: installs dependencies, copies .env, generates key, runs migrations, and builds assets.

### Development Server
```bash
composer dev
```
Starts all development services concurrently:
- Laravel server (port 8000)
- Queue worker (with 1 retry)
- Pail logs (real-time)
- Vite dev server (hot reload)

All services run simultaneously and will stop together when terminated.

### Testing
```bash
composer test
```
Clears config cache and runs PHPUnit test suite.

Run specific test file:
```bash
php artisan test tests/Feature/ExampleTest.php
```

Run specific test method:
```bash
php artisan test --filter test_method_name
```

### Database
```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Drop all tables and re-run migrations
php artisan migrate:fresh --seed # Fresh migration with seeders
php artisan db:seed              # Run seeders only
```

Default database: SQLite (`database/database.sqlite`)

### Asset Building
```bash
npm run dev    # Development mode with hot reload
npm run build  # Production build
```

### Code Quality
```bash
vendor/bin/pint           # Laravel Pint (code formatting)
vendor/bin/pint --test    # Check formatting without changes
```

### Artisan Commands
```bash
php artisan tinker        # REPL for Laravel
php artisan inspire       # Display inspiring quote (custom command)
php artisan queue:work    # Process queue jobs
php artisan pail          # Real-time log viewer
```

## Architecture

### Backend Architecture (CQRS + Clean Architecture)

The backend follows best practices with clear separation of concerns:

**Pattern: CQRS (Command Query Responsibility Segregation)**
- `app/Commands/` - Command objects (write operations)
- `app/Commands/Handlers/` - Command handlers
- Commands are immutable, handlers contain business logic

**Pattern: Service Layer**
- `app/Services/OpenAI/` - OpenAI API integration
- `app/Services/CompetitorAnalysis/` - Core competitor analysis logic
- Services are dependency-injected and testable

**Pattern: DTOs (Data Transfer Objects)**
- `app/DTOs/` - Immutable data structures
- `CompetitorDTO` - Single competitor data
- `CompetitorAnalysisResultDTO` - Analysis result with list of competitors

**Controllers**
- All controllers are **invokable** (single action per controller)
- `app/Http/Controllers/AnalyzeCompetitorsController` - Handles POST `/api/competitors/analyze`
- Controllers delegate to Command Handlers, no business logic

**Validation**
- `app/Http/Requests/AnalyzeCompetitorsRequest` - Form Request with custom validation rules and messages

**API Routes**
- `routes/api.php` - API routes (prefixed with `/api`)
- POST `/api/competitors/analyze` - Main endpoint

### Frontend Architecture (Vue.js 3 + Composition API)

**Vue Components**
- `resources/js/components/CompetitorAnalyzer.vue` - Main component with search form
- `resources/js/components/CompetitorList.vue` - Displays competitor results

**Composables (Reusable Logic)**
- `resources/js/composables/useCompetitorAnalysis.js` - Handles API calls, state management, and business logic
- Uses reactive refs for state (competitors, loading, error)

**Benefits of Composition API:**
- Better code organization and reusability
- TypeScript-friendly (if migrating)
- Easier testing of logic separately from UI

### Environment Configuration

Required environment variables in `.env`:
```
OPENAI_API_KEY=your_openai_api_key_here
```

Database and other configs:
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`

## Coding Standards & Best Practices

### PHP Standards (Laravel 12)

**1. Strict Types**
- **ALWAYS** add `declare(strict_types=1);` after the opening `<?php` tag in ALL PHP files
- This applies to: controllers, models, services, DTOs, commands, routes, etc.

**2. Type Declarations**
- Always use type hints for method parameters
- Always declare return types
- Use nullable types when appropriate (`?string`, `?int`)
- Use union types when needed (PHP 8+)

**3. Immutability**
- DTOs must be `readonly` classes
- Command objects must be `readonly` and immutable
- Use `final` keyword for classes that shouldn't be extended

**4. Controllers (Invokable Pattern)**
- **One action per controller** - Use invokable controllers exclusively
- Controller name should describe the action: `AnalyzeCompetitorsController`, not `CompetitorController`
- Use `__invoke()` method
- Delegate business logic to Command Handlers or Services
- Controllers should only: validate input, delegate work, return responses

**5. CQRS Pattern**
- Separate Commands (write operations) from Queries (read operations)
- Commands are immutable DTOs that represent an intent
- Command Handlers contain the business logic
- Structure:
  - `app/Commands/` - Command objects
  - `app/Commands/Handlers/` - Command handlers

**6. Service Layer**
- Services contain reusable business logic
- Services are injected via constructor (dependency injection)
- Keep services focused on a single responsibility
- Organize by domain: `app/Services/OpenAI/`, `app/Services/CompetitorAnalysis/`

**7. DTOs (Data Transfer Objects)**
- Use `readonly` classes for DTOs
- All properties must be typed
- Provide `toArray()` method for serialization
- Provide `fromArray()` static method for deserialization
- DTOs are immutable - no setters

**8. Form Requests**
- Use Form Requests for validation (not in controllers)
- Define custom error messages
- Define custom attribute names
- Return `true` from `authorize()` if no authentication needed

**9. Code Organization**
```
app/
├── Commands/              # CQRS Commands
│   └── Handlers/         # Command Handlers
├── DTOs/                 # Data Transfer Objects
├── Exceptions/           # Custom Exceptions
├── Http/
│   ├── Controllers/      # Invokable Controllers (one action each)
│   └── Requests/         # Form Requests
├── Models/               # Eloquent Models
├── Providers/            # Service Providers
└── Services/             # Business Logic Services
    ├── OpenAI/
    └── CompetitorAnalysis/
```

### Vue.js 3 Standards (Composition API)

**1. Composition API Only**
- Use `<script setup>` syntax
- No Options API
- Organize logic with composables

**2. Composables**
- Place in `resources/js/composables/`
- Prefix with `use`: `useCompetitorAnalysis.js`
- Return reactive refs and functions
- Composables should be reusable and testable

**3. Component Organization**
- Single File Components (.vue)
- `<template>` first, then `<script setup>`, then `<style>` (if needed)
- Props must be typed with `defineProps`
- Clear component naming: `CompetitorAnalyzer.vue`, `CompetitorList.vue`

**4. State Management**
- Use `ref()` for reactive primitive values
- Use `reactive()` for objects (when appropriate)
- Keep state close to where it's used
- Share state via composables

**5. Component Props**
```javascript
// Good
defineProps({
    competitors: {
        type: Array,
        required: true,
        default: () => []
    }
});
```

### General Principles

**1. Naming Conventions**
- English only for all code (classes, methods, variables)
- Clear, descriptive names that explain intent
- Avoid abbreviations unless widely known

**2. Error Handling**
- Create custom exceptions for domain errors
- Log errors before throwing
- Return meaningful error messages to users
- Use try-catch in services

**3. API Responses**
- Consistent JSON structure:
```json
{
    "success": true,
    "data": {},
    "message": "optional"
}
```

**4. Comments**
- Use PHPDoc for classes and public methods
- Explain "why" not "what" in comments
- Keep comments up to date

## Testing Environment
PHPUnit is configured to use:
- In-memory SQLite database
- Array cache/session drivers
- Synchronous queue processing
- Array mail driver (no emails sent)

This ensures tests run fast and isolated.
