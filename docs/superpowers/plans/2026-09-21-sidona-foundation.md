# SIDONA Foundation (Phase 1 of 5) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the SIDONA Laravel project with authentication, three user roles, campaign (program donasi) management, and the hash chained activity log / login log infrastructure that every later phase builds on.

**Architecture:** Laravel 11 with Livewire 3 full page components (no separate JSON API), Tailwind CSS v4 for styling, SQLite for local development and testing. Authorization uses a Laravel Policy (`CampaignPolicy`) for action level checks plus a route level `role` middleware for page level checks. Every mutating action is written to an `activity_logs` table through a single `AuditLogger` service that chains each entry to the previous one with a SHA256 hash, so tampering with historical rows becomes detectable later (Phase 4 builds the "Verifikasi Integritas" UI on top of the `verifyChain()` method built here).

**Tech Stack:** Laravel 11, Livewire 3, Alpine.js (ships with Livewire), Tailwind CSS v4, SQLite, Pest 3 (`pestphp/pest`, `pestphp/pest-plugin-laravel`).

**Relation to the design spec:** See [`docs/superpowers/specs/2026-09-21-sidona-design.md`](../specs/2026-09-21-sidona-design.md). This phase fully covers ketentuan wajib #1 (Login), #2 (role, though only 3 internal roles exist — donatur guest flow is Phase 2), #3 (Database), #8 (Login log) and #9 (Activity log incl. hash chain). It partially covers #4 (`campaigns` is 1 of the 3 required tabel utama; `donations` and `disbursements` are built in Phases 2 and 3), #6 (validation is demonstrated on the campaign form; the richer donation/disbursement validation is Phase 2/3) and #10 (seeders here cover users and campaigns only).

---

## File Structure

| File | Responsibility |
|---|---|
| `app/Enums/UserRole.php` | Backed enum for the 3 internal roles |
| `app/Enums/CampaignStatus.php` | Backed enum for campaign status |
| `app/Models/User.php` (modify) | Adds `role` cast + `isAdmin()`/`isBendahara()`/`isAuditor()` helpers |
| `app/Models/LoginLog.php` | Read model for `login_logs` |
| `app/Models/ActivityLog.php` | Read model for `activity_logs` |
| `app/Models/Campaign.php` | Program donasi model |
| `app/Services/AuditLogger.php` | Writes hash chained activity log entries, verifies the chain |
| `app/Listeners/LogSuccessfulLogin.php` | Writes a `login_logs` row on successful auth |
| `app/Listeners/LogFailedLogin.php` | Writes a `login_logs` row on failed auth |
| `app/Http/Middleware/EnsureUserHasRole.php` | Route level role gate (`role:bendahara,admin`) |
| `app/Policies/CampaignPolicy.php` | Action level authorization for campaigns |
| `app/Livewire/Auth/LoginForm.php` | Login page logic |
| `app/Livewire/Campaigns/CampaignIndex.php` | Campaign listing + delete action |
| `app/Livewire/Campaigns/CampaignForm.php` | Campaign create/edit form |
| `resources/views/components/layouts/guest.blade.php` | Layout for the login page |
| `resources/views/components/layouts/app.blade.php` | Layout for authenticated pages |
| `resources/views/livewire/auth/login-form.blade.php` | Login form markup |
| `resources/views/livewire/campaigns/campaign-index.blade.php` | Campaign table markup |
| `resources/views/livewire/campaigns/campaign-form.blade.php` | Campaign form markup |
| `database/migrations/*_create_login_logs_table.php` | `login_logs` schema |
| `database/migrations/*_create_activity_logs_table.php` | `activity_logs` schema |
| `database/migrations/*_create_campaigns_table.php` | `campaigns` schema |
| `database/factories/CampaignFactory.php` | Test data factory for campaigns |
| `database/seeders/UserSeeder.php` | Seeds 1 admin, 2 bendahara, 2 auditor |
| `database/seeders/CampaignSeeder.php` | Seeds 6 to 8 sample campaigns |
| `routes/web.php` (modify) | Login, logout, dashboard, campaign routes |
| `bootstrap/app.php` (modify) | Registers the `role` middleware alias |
| `app/Providers/AppServiceProvider.php` (modify) | Registers the login/failed login listeners |

---

## Task 1: Scaffold the Laravel project

**Files:**
- Create: entire Laravel skeleton at the repository root (`/Users/mac/Dev/sidona`)

- [ ] **Step 1: Scaffold Laravel into a temporary directory and merge it into the repo**

The repo root already contains `.git/` and `docs/`, so `composer create-project` cannot target it directly (it refuses non-empty directories). Scaffold into a temp folder, then merge.

```bash
cd /Users/mac/Dev/sidona
composer create-project laravel/laravel _scaffold "^11.0"
rsync -a _scaffold/ ./ --exclude=.git
rm -rf _scaffold
```

- [ ] **Step 2: Verify the install**

Run: `php artisan --version`
Expected: output starting with `Laravel Framework 11.`

- [ ] **Step 3: Commit**

```bash
git add -A
git commit -m "chore: scaffold Laravel 11 project"
```

---

## Task 2: Configure SQLite and verify the baseline migration

**Files:**
- Modify: `.env`, `.gitignore`

- [ ] **Step 1: Confirm SQLite is configured**

Open `.env` and confirm it contains:

```
DB_CONNECTION=sqlite
```

If `DB_DATABASE` is set to anything else, remove that line entirely (Laravel 11 defaults `DB_DATABASE` to `database/database.sqlite` automatically when `DB_CONNECTION=sqlite`).

- [ ] **Step 2: Create the SQLite file if it does not already exist**

```bash
test -f database/database.sqlite || touch database/database.sqlite
```

- [ ] **Step 3: Keep the local database file out of git**

Add this line to `.gitignore`:

```
/database/database.sqlite
```

- [ ] **Step 4: Run the baseline migration**

Run: `php artisan migrate`
Expected: output ending with `INFO  Nothing to migrate.` or a list of the default `users`, `cache`, `jobs` migrations running successfully, no errors.

- [ ] **Step 5: Commit**

```bash
git add .gitignore
git commit -m "chore: configure sqlite for local development"
```

---

## Task 3: Install Tailwind CSS v4

**Files:**
- Modify: `vite.config.js`, `resources/css/app.css`, `resources/views/welcome.blade.php`

- [ ] **Step 1: Install the packages**

```bash
npm install tailwindcss @tailwindcss/vite
```

- [ ] **Step 2: Wire the Vite plugin**

Replace the contents of `vite.config.js` with:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

- [ ] **Step 3: Import Tailwind in the stylesheet**

Replace the contents of `resources/css/app.css` with:

```css
@import "tailwindcss";

@source "../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php";
@source "../../storage/framework/views/*.php";
@source "../**/*.blade.php";
@source "../**/*.js";
```

- [ ] **Step 4: Delete the default welcome page**

It references asset paths that no longer make sense once we add our own layouts.

```bash
rm resources/views/welcome.blade.php
```

- [ ] **Step 5: Verify the build works**

Run: `npm run build`
Expected: exits 0, prints a `public/build/manifest.json` output line.

- [ ] **Step 6: Commit**

```bash
git add package.json package-lock.json vite.config.js resources/css/app.css
git rm resources/views/welcome.blade.php
git commit -m "chore: install Tailwind CSS v4"
```

---

## Task 4: Install Livewire 3

**Files:**
- Create: `app/Livewire/SmokeTest.php`, `resources/views/livewire/smoke-test.blade.php`, `tests/Feature/LivewireSmokeTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Install the package**

```bash
composer require livewire/livewire
```

- [ ] **Step 2: Write a failing smoke test**

Create `tests/Feature/LivewireSmokeTest.php`:

```php
<?php

use App\Livewire\SmokeTest;
use Livewire\Livewire;

it('renders the livewire smoke test component', function () {
    Livewire::test(SmokeTest::class)
        ->assertSee('SIDONA siap jalan');
});
```

- [ ] **Step 3: Run it and confirm it fails**

Run: `php artisan test --filter=LivewireSmokeTest`
Expected: FAIL — `Class "App\Livewire\SmokeTest" not found`

- [ ] **Step 4: Create the component**

Create `app/Livewire/SmokeTest.php`:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class SmokeTest extends Component
{
    public function render()
    {
        return view('livewire.smoke-test');
    }
}
```

Create `resources/views/livewire/smoke-test.blade.php`:

```blade
<div>SIDONA siap jalan</div>
```

- [ ] **Step 5: Run the test again and confirm it passes**

Run: `php artisan test --filter=LivewireSmokeTest`
Expected: PASS

- [ ] **Step 6: Delete the smoke test files**

They were only there to prove Livewire is wired correctly; the real components come in later tasks.

```bash
rm app/Livewire/SmokeTest.php resources/views/livewire/smoke-test.blade.php tests/Feature/LivewireSmokeTest.php
```

- [ ] **Step 7: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: install Livewire 3"
```

---

## Task 5: Install Pest

**Files:**
- Create: `tests/Pest.php` (generated, then modified)
- Delete: `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`

- [ ] **Step 1: Install Pest and the Laravel plugin**

```bash
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev
php artisan pest:install
```

Answer `yes` if prompted to replace PHPUnit as the test runner.

- [ ] **Step 2: Remove the example tests**

```bash
rm tests/Feature/ExampleTest.php tests/Unit/ExampleTest.php
```

- [ ] **Step 3: Apply RefreshDatabase to every Feature test by default**

Replace the contents of `tests/Pest.php` with:

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
```

- [ ] **Step 4: Verify the test suite runs with zero tests**

Run: `php artisan test`
Expected: `Tests:  0 tests` (or similar), exit code 0, no errors.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "chore: install Pest testing framework"
```

---

## Task 6: User roles

**Files:**
- Create: `app/Enums/UserRole.php`
- Modify: `database/migrations/0001_01_01_000000_create_users_table.php`, `app/Models/User.php`
- Test: `tests/Feature/UserRoleTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/UserRoleTest.php`:

```php
<?php

use App\Enums\UserRole;
use App\Models\User;

it('casts the role attribute to the UserRole enum', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    expect($user->fresh()->role)->toBe(UserRole::Admin);
});

it('exposes role helper methods', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($admin->isAdmin())->toBeTrue();
    expect($bendahara->isBendahara())->toBeTrue();
    expect($auditor->isAuditor())->toBeTrue();
    expect($admin->isBendahara())->toBeFalse();
});

it('defaults new users to the auditor role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::Auditor);
});
```

- [ ] **Step 2: Run it and confirm it fails**

Run: `php artisan test --filter=UserRoleTest`
Expected: FAIL — `Class "App\Enums\UserRole" not found`

- [ ] **Step 3: Create the enum**

Create `app/Enums/UserRole.php`:

```php
<?php

namespace App\Enums;

enum UserRole: string
{
    case Bendahara = 'bendahara';
    case Admin = 'admin';
    case Auditor = 'auditor';

    public function label(): string
    {
        return match ($this) {
            self::Bendahara => 'Bendahara',
            self::Admin => 'Admin',
            self::Auditor => 'Auditor',
        };
    }
}
```

- [ ] **Step 4: Add the `role` column to the users migration**

In `database/migrations/0001_01_01_000000_create_users_table.php`, inside the `Schema::create('users', ...)` closure, add the new column right after `$table->string('password');`:

```php
            $table->string('password');
            $table->string('role')->default('auditor');
            $table->rememberToken();
```

- [ ] **Step 5: Update the User model**

In `app/Models/User.php`, add the import and update `$fillable` and `casts()`:

```php
use App\Enums\UserRole;
```

```php
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];
```

```php
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isBendahara(): bool
    {
        return $this->role === UserRole::Bendahara;
    }

    public function isAuditor(): bool
    {
        return $this->role === UserRole::Auditor;
    }
```

- [ ] **Step 6: Refresh the test database and run the test again**

Run: `php artisan migrate:fresh && php artisan test --filter=UserRoleTest`
Expected: PASS (3 tests)

- [ ] **Step 7: Commit**

```bash
git add app/Enums/UserRole.php app/Models/User.php database/migrations tests/Feature/UserRoleTest.php
git commit -m "feat: add UserRole enum and role column to users"
```

---

## Task 7: Login log

**Files:**
- Create: `database/migrations/*_create_login_logs_table.php`, `app/Models/LoginLog.php`

- [ ] **Step 1: Generate the migration**

```bash
php artisan make:migration create_login_logs_table
```

- [ ] **Step 2: Write the schema**

Open the generated file in `database/migrations/` and replace its contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
```

- [ ] **Step 3: Create the model**

Create `app/Models/LoginLog.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 4: Run the migration**

Run: `php artisan migrate`
Expected: `login_logs` table created, no errors.

- [ ] **Step 5: Commit**

```bash
git add database/migrations app/Models/LoginLog.php
git commit -m "feat: add login_logs table and model"
```

---

## Task 8: Record logins automatically

**Files:**
- Create: `app/Listeners/LogSuccessfulLogin.php`, `app/Listeners/LogFailedLogin.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Feature/LoginLoggingTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/LoginLoggingTest.php`:

```php
<?php

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;

it('records a login log entry when a user authenticates successfully', function () {
    $user = User::factory()->create();

    event(new Login('web', $user, false));

    expect(LoginLog::query()
        ->where('user_id', $user->id)
        ->where('status', 'success')
        ->exists())->toBeTrue();
});

it('records a login log entry when authentication fails', function () {
    event(new Failed('web', null, ['email' => 'tidak-ada@sidona.test', 'password' => 'salah']));

    expect(LoginLog::query()
        ->whereNull('user_id')
        ->where('email', 'tidak-ada@sidona.test')
        ->where('status', 'failed')
        ->exists())->toBeTrue();
});
```

- [ ] **Step 2: Run it and confirm it fails**

Run: `php artisan test --filter=LoginLoggingTest`
Expected: FAIL — no `login_logs` rows created, since nothing listens for the events yet.

- [ ] **Step 3: Create the listeners**

Create `app/Listeners/LogSuccessfulLogin.php`:

```php
<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        LoginLog::create([
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent(),
            'status' => 'success',
        ]);
    }
}
```

Create `app/Listeners/LogFailedLogin.php`:

```php
<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        LoginLog::create([
            'user_id' => $event->user?->id,
            'email' => $event->credentials['email'] ?? 'unknown',
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent(),
            'status' => 'failed',
        ]);
    }
}
```

- [ ] **Step 4: Register the listeners**

In `app/Providers/AppServiceProvider.php`, add the imports:

```php
use App\Listeners\LogFailedLogin;
use App\Listeners\LogSuccessfulLogin;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
```

Replace the empty `boot()` method body:

```php
    public function boot(): void
    {
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Failed::class, LogFailedLogin::class);
    }
```

- [ ] **Step 5: Run the test again and confirm it passes**

Run: `php artisan test --filter=LoginLoggingTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Listeners app/Providers/AppServiceProvider.php tests/Feature/LoginLoggingTest.php
git commit -m "feat: record login attempts into login_logs"
```

---

## Task 9: Login page

**Files:**
- Create: `app/Livewire/Auth/LoginForm.php`, `resources/views/components/layouts/guest.blade.php`, `resources/views/livewire/auth/login-form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/LoginPageTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/LoginPageTest.php`:

```php
<?php

use App\Livewire\Auth\LoginForm;
use App\Models\User;
use Livewire\Livewire;

it('logs a user in with correct credentials and redirects to the dashboard', function () {
    $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

    Livewire::test(LoginForm::class)
        ->set('email', $user->email)
        ->set('password', 'rahasia123')
        ->call('authenticate')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password', function () {
    $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

    Livewire::test(LoginForm::class)
        ->set('email', $user->email)
        ->set('password', 'salah')
        ->call('authenticate')
        ->assertHasErrors('email');

    $this->assertGuest();
});
```

- [ ] **Step 2: Run it and confirm it fails**

Run: `php artisan test --filter=LoginPageTest`
Expected: FAIL — `Class "App\Livewire\Auth\LoginForm" not found`

- [ ] **Step 3: Create the guest layout**

Create `resources/views/components/layouts/guest.blade.php`:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIDONA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-900">
    {{ $slot }}
    @livewireScripts
</body>
</html>
```

- [ ] **Step 4: Create the LoginForm component**

Create `app/Livewire/Auth/LoginForm.php`:

```php
<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';

    public function authenticate(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
```

- [ ] **Step 5: Create the view**

Create `resources/views/livewire/auth/login-form.blade.php`:

```blade
<div class="max-w-sm mx-auto mt-24">
    <h1 class="text-xl font-semibold mb-6 text-center">Masuk ke SIDONA</h1>

    <form wire:submit="authenticate" class="space-y-4 bg-white p-6 rounded border border-slate-300">
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" wire:model="email" class="w-full rounded border border-slate-300 px-3 py-2">
            @error('email') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Kata Sandi</label>
            <input type="password" wire:model="password" class="w-full rounded border border-slate-300 px-3 py-2">
            @error('password') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2 text-white text-sm">Masuk</button>
    </form>
</div>
```

- [ ] **Step 6: Add the routes**

Replace the contents of `routes/web.php` with:

```php
<?php

use App\Livewire\Auth\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', LoginForm::class)->name('login');
});

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('campaigns.index');
    })->name('dashboard');
});
```

- [ ] **Step 7: Run the test again and confirm it passes**

Run: `php artisan test --filter=LoginPageTest`
Expected: PASS (2 tests)

Note: `dashboard` currently redirects into `campaigns.index`, which does not exist until Task 13 to 15. That is fine — this test only checks the redirect target URL, it does not follow it.

- [ ] **Step 8: Commit**

```bash
git add app/Livewire resources/views/components/layouts/guest.blade.php resources/views/livewire/auth routes/web.php tests/Feature/LoginPageTest.php
git commit -m "feat: add login page"
```

---

## Task 10: Role based route access

**Files:**
- Create: `app/Http/Middleware/EnsureUserHasRole.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Feature/RoleMiddlewareTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/RoleMiddlewareTest.php`:

```php
<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'role:admin'])
        ->get('/_test/admin-only', fn () => 'ok');
});

it('allows a user with the required role', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get('/_test/admin-only')->assertOk();
});

it('blocks a user without the required role', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    $this->actingAs($auditor)->get('/_test/admin-only')->assertForbidden();
});
```

- [ ] **Step 2: Run it and confirm it fails**

Run: `php artisan test --filter=RoleMiddlewareTest`
Expected: FAIL — the `role` middleware alias does not exist yet (500 or "Target class [role] does not exist").

- [ ] **Step 3: Create the middleware**

Create `app/Http/Middleware/EnsureUserHasRole.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Register the middleware alias**

In `bootstrap/app.php`, replace:

```php
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
```

with:

```php
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
```

- [ ] **Step 5: Run the test again and confirm it passes**

Run: `php artisan test --filter=RoleMiddlewareTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/EnsureUserHasRole.php bootstrap/app.php tests/Feature/RoleMiddlewareTest.php
git commit -m "feat: add role based route middleware"
```

---

## Task 11: Activity log table

**Files:**
- Create: `database/migrations/*_create_activity_logs_table.php`, `app/Models/ActivityLog.php`

- [ ] **Step 1: Generate the migration**

```bash
php artisan make:migration create_activity_logs_table
```

- [ ] **Step 2: Write the schema**

Open the generated file and replace its contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('before');
            $table->json('after');
            $table->string('prev_hash', 64);
            $table->string('hash', 64);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
```

- [ ] **Step 3: Create the model**

Create `app/Models/ActivityLog.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'before',
        'after',
        'prev_hash',
        'hash',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 4: Run the migration**

Run: `php artisan migrate`
Expected: `activity_logs` table created, no errors.

- [ ] **Step 5: Commit**

```bash
git add database/migrations app/Models/ActivityLog.php
git commit -m "feat: add activity_logs table and model"
```

---

## Task 12: Hash chained AuditLogger service

This is the core "bisa dipakai untuk audit" feature: every entry cryptographically links to the one before it, so an Auditor can later prove whether the log has been tampered with.

**Files:**
- Create: `app/Services/AuditLogger.php`
- Test: `tests/Feature/AuditLoggerTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/AuditLoggerTest.php`:

```php
<?php

use App\Models\User;
use App\Services\AuditLogger;

it('chains each new entry to the hash of the previous entry', function () {
    $logger = app(AuditLogger::class);
    $user = User::factory()->create();

    $first = $logger->log('campaign.created', $user, after: ['name' => 'Donasi Gempa']);
    $second = $logger->log('campaign.updated', $user, before: ['name' => 'Donasi Gempa'], after: ['name' => 'Donasi Gempa Cianjur']);

    expect($first->prev_hash)->toBe(AuditLogger::genesisHash());
    expect($second->prev_hash)->toBe($first->hash);
    expect($second->hash)->not->toBe($first->hash);
});

it('reports the chain as valid when nothing has been tampered with', function () {
    $logger = app(AuditLogger::class);
    $user = User::factory()->create();

    $logger->log('campaign.created', $user, after: ['name' => 'Donasi Gempa']);
    $logger->log('campaign.updated', $user, before: ['name' => 'Donasi Gempa'], after: ['name' => 'Donasi Gempa Cianjur']);

    expect($logger->verifyChain())->toBe(['valid' => true, 'tampered_at' => null]);
});

it('detects a row that was changed outside of AuditLogger', function () {
    $logger = app(AuditLogger::class);
    $user = User::factory()->create();

    $logger->log('campaign.created', $user, after: ['name' => 'Donasi Gempa']);
    $tampered = $logger->log('campaign.updated', $user, before: ['name' => 'Donasi Gempa'], after: ['name' => 'Donasi Gempa Cianjur']);

    $tampered->forceFill(['action' => 'campaign.deleted'])->saveQuietly();

    $result = $logger->verifyChain();

    expect($result['valid'])->toBeFalse();
    expect($result['tampered_at'])->toBe($tampered->id);
});
```

- [ ] **Step 2: Run it and confirm it fails**

Run: `php artisan test --filter=AuditLoggerTest`
Expected: FAIL — `Class "App\Services\AuditLogger" not found`

- [ ] **Step 3: Implement the service**

Create `app/Services/AuditLogger.php`:

```php
<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function genesisHash(): string
    {
        return str_repeat('0', 64);
    }

    public function log(string $action, ?User $user, ?Model $subject = null, array $before = [], array $after = []): ActivityLog
    {
        return DB::transaction(function () use ($action, $user, $subject, $before, $after) {
            $previous = ActivityLog::query()->orderByDesc('id')->lockForUpdate()->first();
            $prevHash = $previous->hash ?? self::genesisHash();

            $payload = [
                'action' => $action,
                'user_id' => $user?->id,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'before' => $before,
                'after' => $after,
            ];

            $hash = hash('sha256', $prevHash.json_encode($payload, JSON_UNESCAPED_SLASHES));

            return ActivityLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'before' => $before,
                'after' => $after,
                'prev_hash' => $prevHash,
                'hash' => $hash,
            ]);
        });
    }

    /**
     * @return array{valid: bool, tampered_at: int|null}
     */
    public function verifyChain(): array
    {
        $expectedPrevHash = self::genesisHash();
        $tamperedAt = null;

        foreach (ActivityLog::query()->orderBy('id')->cursor() as $entry) {
            $payload = [
                'action' => $entry->action,
                'user_id' => $entry->user_id,
                'subject_type' => $entry->subject_type,
                'subject_id' => $entry->subject_id,
                'before' => $entry->before ?? [],
                'after' => $entry->after ?? [],
            ];

            $expectedHash = hash('sha256', $expectedPrevHash.json_encode($payload, JSON_UNESCAPED_SLASHES));

            if ($entry->prev_hash !== $expectedPrevHash || $entry->hash !== $expectedHash) {
                $tamperedAt = $entry->id;
                break;
            }

            $expectedPrevHash = $entry->hash;
        }

        return [
            'valid' => $tamperedAt === null,
            'tampered_at' => $tamperedAt,
        ];
    }
}
```

- [ ] **Step 4: Run the tests again and confirm they pass**

Run: `php artisan test --filter=AuditLoggerTest`
Expected: PASS (3 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Services/AuditLogger.php tests/Feature/AuditLoggerTest.php
git commit -m "feat: add hash chained AuditLogger service"
```

---

## Task 13: Campaign model

**Files:**
- Create: `app/Enums/CampaignStatus.php`, `database/migrations/*_create_campaigns_table.php`, `app/Models/Campaign.php`, `database/factories/CampaignFactory.php`

- [ ] **Step 1: Create the status enum**

Create `app/Enums/CampaignStatus.php`:

```php
<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Active = 'active';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
        };
    }
}
```

- [ ] **Step 2: Generate and write the migration**

```bash
php artisan make:migration create_campaigns_table
```

Open the generated file and replace its contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('target_amount');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
```

- [ ] **Step 3: Create the model**

Create `app/Models/Campaign.php`:

```php
<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'target_amount',
        'starts_on',
        'ends_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'status' => CampaignStatus::class,
        ];
    }
}
```

- [ ] **Step 4: Create the factory**

Create `database/factories/CampaignFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Donasi '.fake()->words(3, true),
            'description' => fake()->sentence(),
            'target_amount' => fake()->numberBetween(1_000_000, 100_000_000),
            'starts_on' => now()->subDays(10)->toDateString(),
            'ends_on' => now()->addDays(30)->toDateString(),
            'status' => CampaignStatus::Active,
        ];
    }
}
```

- [ ] **Step 5: Run the migration and verify**

Run: `php artisan migrate`
Expected: `campaigns` table created, no errors.

Run: `php artisan tinker --execute="dd(App\Models\Campaign::factory()->create()->fresh()->toArray());"`
Expected: prints an array with `status` equal to `active` and no error.

- [ ] **Step 6: Commit**

```bash
git add app/Enums/CampaignStatus.php database/migrations app/Models/Campaign.php database/factories/CampaignFactory.php
git commit -m "feat: add Campaign model"
```

---

## Task 14: Campaign authorization policy

**Files:**
- Create: `app/Policies/CampaignPolicy.php`
- Test: `tests/Feature/CampaignPolicyTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/CampaignPolicyTest.php`:

```php
<?php

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

it('lets bendahara and admin create campaigns but not auditor', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($bendahara->can('create', Campaign::class))->toBeTrue();
    expect($admin->can('create', Campaign::class))->toBeTrue();
    expect($auditor->can('create', Campaign::class))->toBeFalse();
});

it('only lets admin delete campaigns', function () {
    $campaign = Campaign::factory()->create();
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    expect($bendahara->can('delete', $campaign))->toBeFalse();
    expect($admin->can('delete', $campaign))->toBeTrue();
});

it('lets every role view campaigns', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    expect($auditor->can('viewAny', Campaign::class))->toBeTrue();
});
```

- [ ] **Step 2: Run it and confirm it fails**

Run: `php artisan test --filter=CampaignPolicyTest`
Expected: FAIL — all `can()` checks return `false` because no policy is registered yet.

- [ ] **Step 3: Create the policy**

Create `app/Policies/CampaignPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Bendahara, UserRole::Admin], true);
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return in_array($user->role, [UserRole::Bendahara, UserRole::Admin], true);
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->role === UserRole::Admin;
    }
}
```

Laravel auto discovers this policy because `App\Models\Campaign` and `App\Policies\CampaignPolicy` follow the standard naming convention, no manual registration needed.

- [ ] **Step 4: Run the test again and confirm it passes**

Run: `php artisan test --filter=CampaignPolicyTest`
Expected: PASS (3 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Policies/CampaignPolicy.php tests/Feature/CampaignPolicyTest.php
git commit -m "feat: add CampaignPolicy"
```

---

## Task 15: Campaign management pages

**Files:**
- Create: `app/Livewire/Campaigns/CampaignIndex.php`, `app/Livewire/Campaigns/CampaignForm.php`, `resources/views/components/layouts/app.blade.php`, `resources/views/livewire/campaigns/campaign-index.blade.php`, `resources/views/livewire/campaigns/campaign-form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/CampaignManagementTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/CampaignManagementTest.php`:

```php
<?php

use App\Enums\UserRole;
use App\Livewire\Campaigns\CampaignForm;
use App\Livewire\Campaigns\CampaignIndex;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

it('blocks an auditor from opening the campaign creation route', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    $this->actingAs($auditor)->get(route('campaigns.create'))->assertForbidden();
});

it('lets a bendahara open the campaign creation route', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);

    $this->actingAs($bendahara)->get(route('campaigns.create'))->assertOk();
});

it('creates a campaign and writes an activity log entry', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(CampaignForm::class)
        ->set('name', 'Donasi Gempa Cianjur')
        ->set('description', 'Bantuan korban gempa')
        ->set('target_amount', 50000000)
        ->set('starts_on', '2026-01-01')
        ->set('ends_on', '2026-03-01')
        ->call('save')
        ->assertRedirect(route('campaigns.index'));

    $campaign = Campaign::first();

    expect($campaign->name)->toBe('Donasi Gempa Cianjur');
    expect(ActivityLog::where('action', 'campaign.created')
        ->where('subject_id', $campaign->id)
        ->exists())->toBeTrue();
});

it('rejects a campaign whose end date is before its start date', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(CampaignForm::class)
        ->set('name', 'Donasi Tidak Valid')
        ->set('target_amount', 1000000)
        ->set('starts_on', '2026-03-01')
        ->set('ends_on', '2026-01-01')
        ->call('save')
        ->assertHasErrors('ends_on');

    expect(Campaign::count())->toBe(0);
});

it('prevents an auditor from deleting a campaign', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($auditor)
        ->test(CampaignIndex::class)
        ->call('delete', $campaign)
        ->assertForbidden();

    expect(Campaign::find($campaign->id))->not->toBeNull();
});

it('lets an admin delete a campaign and logs it', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($admin)
        ->test(CampaignIndex::class)
        ->call('delete', $campaign);

    expect(Campaign::find($campaign->id))->toBeNull();
    expect(ActivityLog::where('action', 'campaign.deleted')
        ->where('subject_id', $campaign->id)
        ->exists())->toBeTrue();
});
```

- [ ] **Step 2: Run it and confirm it fails**

Run: `php artisan test --filter=CampaignManagementTest`
Expected: FAIL — routes and components do not exist yet.

- [ ] **Step 3: Create the authenticated layout**

Create `resources/views/components/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIDONA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-900">
    <nav class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="font-semibold">SIDONA</a>
        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('campaigns.index') }}" wire:navigate>Program Donasi</a>
            <span class="text-slate-400">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-8">
        @if (session('status'))
            <div class="mb-4 rounded border border-emerald-600 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
```

- [ ] **Step 4: Create the CampaignForm component**

Create `app/Livewire/Campaigns/CampaignForm.php`:

```php
<?php

namespace App\Livewire\Campaigns;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CampaignForm extends Component
{
    public ?Campaign $campaign = null;

    public string $name = '';
    public string $description = '';
    public int $target_amount = 0;
    public string $starts_on = '';
    public string $ends_on = '';

    public function mount(?Campaign $campaign = null): void
    {
        Gate::authorize($campaign ? 'update' : 'create', $campaign ?? Campaign::class);

        $this->campaign = $campaign;

        if ($campaign) {
            $this->name = $campaign->name;
            $this->description = (string) $campaign->description;
            $this->target_amount = $campaign->target_amount;
            $this->starts_on = $campaign->starts_on->format('Y-m-d');
            $this->ends_on = $campaign->ends_on->format('Y-m-d');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['required', 'integer', 'min:10000'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
        ];
    }

    public function save(AuditLogger $logger): void
    {
        Gate::authorize($this->campaign ? 'update' : 'create', $this->campaign ?? Campaign::class);

        $data = $this->validate();

        if ($this->campaign) {
            $before = $this->campaign->only(array_keys($data));
            $this->campaign->update($data);
            $logger->log('campaign.updated', auth()->user(), $this->campaign, $before, $data);
        } else {
            $data['status'] = CampaignStatus::Active;
            $campaign = Campaign::create($data);
            $logger->log('campaign.created', auth()->user(), $campaign, [], $data);
        }

        session()->flash('status', 'Program donasi berhasil disimpan.');

        $this->redirectRoute('campaigns.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-form');
    }
}
```

- [ ] **Step 5: Create the CampaignForm view**

Create `resources/views/livewire/campaigns/campaign-form.blade.php`:

```blade
<div class="max-w-xl">
    <h1 class="text-xl font-semibold mb-6">{{ $campaign ? 'Ubah Program Donasi' : 'Tambah Program Donasi' }}</h1>

    <form wire:submit="save" class="space-y-4 bg-white p-6 rounded border border-slate-300">
        <div>
            <label class="block text-sm font-medium mb-1">Nama Program</label>
            <input type="text" wire:model="name" class="w-full rounded border border-slate-300 px-3 py-2">
            @error('name') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea wire:model="description" class="w-full rounded border border-slate-300 px-3 py-2"></textarea>
            @error('description') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Target Dana (Rupiah)</label>
            <input type="number" wire:model="target_amount" class="w-full rounded border border-slate-300 px-3 py-2">
            @error('target_amount') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Mulai</label>
                <input type="date" wire:model="starts_on" class="w-full rounded border border-slate-300 px-3 py-2">
                @error('starts_on') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Selesai</label>
                <input type="date" wire:model="ends_on" class="w-full rounded border border-slate-300 px-3 py-2">
                @error('ends_on') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-white text-sm">Simpan</button>
    </form>
</div>
```

- [ ] **Step 6: Create the CampaignIndex component**

Create `app/Livewire/Campaigns/CampaignIndex.php`:

```php
<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CampaignIndex extends Component
{
    public function delete(Campaign $campaign, AuditLogger $logger): void
    {
        Gate::authorize('delete', $campaign);

        $before = $campaign->only(['name', 'description', 'target_amount', 'starts_on', 'ends_on', 'status']);
        $campaign->delete();
        $logger->log('campaign.deleted', auth()->user(), $campaign, $before, []);

        session()->flash('status', 'Program donasi dihapus.');
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-index', [
            'campaigns' => Campaign::query()->latest()->paginate(10),
        ]);
    }
}
```

- [ ] **Step 7: Create the CampaignIndex view**

Create `resources/views/livewire/campaigns/campaign-index.blade.php`:

```blade
<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Program Donasi</h1>
        @can('create', App\Models\Campaign::class)
            <a href="{{ route('campaigns.create') }}" wire:navigate class="rounded bg-slate-900 px-4 py-2 text-white text-sm">Tambah Program</a>
        @endcan
    </div>

    <table class="w-full border border-slate-300 text-sm bg-white">
        <thead class="bg-slate-200">
            <tr>
                <th class="border border-slate-300 px-3 py-2 text-left">Nama</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Target</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Periode</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Status</th>
                <th class="border border-slate-300 px-3 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campaigns as $campaign)
                <tr>
                    <td class="border border-slate-300 px-3 py-2">{{ $campaign->name }}</td>
                    <td class="border border-slate-300 px-3 py-2">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $campaign->starts_on->format('d/m/Y') }} sampai {{ $campaign->ends_on->format('d/m/Y') }}</td>
                    <td class="border border-slate-300 px-3 py-2">{{ $campaign->status->label() }}</td>
                    <td class="border border-slate-300 px-3 py-2 space-x-2">
                        @can('update', $campaign)
                            <a href="{{ route('campaigns.edit', $campaign) }}" wire:navigate>Ubah</a>
                        @endcan
                        @can('delete', $campaign)
                            <button type="button" wire:click="delete({{ $campaign->id }})" wire:confirm="Yakin ingin menghapus program ini?">Hapus</button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="border border-slate-300 px-3 py-6 text-center text-slate-500">Belum ada program donasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
```

- [ ] **Step 8: Add the campaign routes**

In `routes/web.php`, add the imports:

```php
use App\Livewire\Campaigns\CampaignForm;
use App\Livewire\Campaigns\CampaignIndex;
```

Replace the `dashboard` route block with:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('campaigns.index');
    })->name('dashboard');

    Route::get('/campaigns', CampaignIndex::class)->name('campaigns.index');
    Route::get('/campaigns/create', CampaignForm::class)
        ->middleware('role:bendahara,admin')
        ->name('campaigns.create');
    Route::get('/campaigns/{campaign}/edit', CampaignForm::class)
        ->middleware('role:bendahara,admin')
        ->name('campaigns.edit');
});
```

- [ ] **Step 9: Run the tests again and confirm they pass**

Run: `php artisan test --filter=CampaignManagementTest`
Expected: PASS (6 tests)

- [ ] **Step 10: Run the full test suite**

Run: `php artisan test`
Expected: all tests across every task so far pass.

- [ ] **Step 11: Commit**

```bash
git add app/Livewire/Campaigns resources/views/components/layouts/app.blade.php resources/views/livewire/campaigns routes/web.php tests/Feature/CampaignManagementTest.php
git commit -m "feat: add campaign management pages"
```

---

## Task 16: Seed data

**Files:**
- Create: `database/seeders/UserSeeder.php`, `database/seeders/CampaignSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create the user seeder**

Create `database/seeders/UserSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin SIDONA',
            'email' => 'admin@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin,
        ]);

        User::factory()->create([
            'name' => 'Bendahara Satu',
            'email' => 'bendahara1@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Bendahara,
        ]);

        User::factory()->create([
            'name' => 'Bendahara Dua',
            'email' => 'bendahara2@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Bendahara,
        ]);

        User::factory()->create([
            'name' => 'Auditor Satu',
            'email' => 'auditor1@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Auditor,
        ]);

        User::factory()->create([
            'name' => 'Auditor Dua',
            'email' => 'auditor2@sidona.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Auditor,
        ]);
    }
}
```

- [ ] **Step 2: Create the campaign seeder**

Create `database/seeders/CampaignSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(AuditLogger $logger): void
    {
        $admin = User::query()->where('email', 'admin@sidona.test')->firstOrFail();

        $names = [
            'Donasi Gempa Cianjur',
            'Donasi Pendidikan Anak Yatim',
            'Donasi Korban Banjir Demak',
            'Donasi Renovasi Panti Asuhan',
            'Donasi Beasiswa Mahasiswa',
            'Donasi Bantuan Pangan Lansia',
        ];

        foreach ($names as $index => $name) {
            $status = $index < 4 ? CampaignStatus::Active : CampaignStatus::Completed;

            $campaign = Campaign::factory()->create([
                'name' => $name,
                'status' => $status,
            ]);

            $logger->log('campaign.created', $admin, $campaign, [], $campaign->only([
                'name', 'description', 'target_amount', 'starts_on', 'ends_on', 'status',
            ]));
        }
    }
}
```

- [ ] **Step 3: Wire both seeders into DatabaseSeeder**

Replace the contents of `database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CampaignSeeder::class,
        ]);
    }
}
```

- [ ] **Step 4: Run the seeders against a fresh database**

Run: `php artisan migrate:fresh --seed`
Expected: exits 0, no errors.

- [ ] **Step 5: Verify the hash chain has real data**

Run: `php artisan tinker --execute="dd(app(App\Services\AuditLogger::class)->verifyChain());"`
Expected: `['valid' => true, 'tampered_at' => null]`

- [ ] **Step 6: Run the full test suite one more time**

Run: `php artisan test`
Expected: all tests pass (seeders are not run by the test suite, `RefreshDatabase` only runs migrations, so this confirms the seeders did not break anything they touch, such as the `AuditLogger` service and models).

- [ ] **Step 7: Commit**

```bash
git add database/seeders
git commit -m "feat: seed users and campaigns"
```

---

## Manual verification checklist

After Task 16, manually confirm the phase works end to end before moving on to Phase 2 (donation transactions):

1. `npm run dev` in one terminal, `php artisan serve` in another.
2. Visit `/login`, sign in as `admin@sidona.test` / `password` — should land on `/campaigns`.
3. Log out, sign in as `auditor1@sidona.test` / `password` — should see the campaign list but no "Tambah Program" button, and visiting `/campaigns/create` directly should show a 403 page.
4. Log out, sign in as `bendahara1@sidona.test` / `password` — should be able to create and edit a campaign.
5. Check `database/database.sqlite` (via `php artisan tinker`) that `login_logs` has rows for every login above, and `activity_logs` has rows for every campaign created through the seeder and through the UI, each with a non empty `hash` and `prev_hash`.
