# SIDONA Audit Features (Phase 4 of 5) Implementation Plan

**Context:** Phases 1–3 (foundation, donation transactions, disbursement transactions) are done and code-reviewed on branch `phase-1-foundation`. The hash-chained `AuditLogger` (`app/Services/AuditLogger.php`) has been writing tamper-evident log entries since Phase 1, but nothing surfaces it yet. This phase builds the features that make SIDONA "usable as an audit tool" per spec §6: the Verifikasi Integritas page that proves the chain hasn't been tampered with, a filterable audit trail viewer over `activity_logs`/`login_logs`, and an anomaly dashboard. Per the role table (spec §3), all of these are **Auditor-exclusive** — Admin/Bendahara don't get them.

**Scope:** Spec §6.1 (hash chain integrity + demo tamper command), §6.3 (anomaly dashboard), §6.4 (audit trail viewer). §6.2 (PDF export with checksum) and §7 (Laporan) are Phase 5.

**Reused:** `AuditLogger::verifyChain()`/`genesisHash()` (`app/Services/AuditLogger.php`, built in Phase 1, already tested in `tests/Feature/AuditLoggerTest.php`) — this phase only adds a UI on top, no changes to the service itself. `EnsureUserHasRole` middleware alias (`role:auditor`). The `#[Url]`-backed filter pattern from `DonationIndex`/`DisbursementIndex` (`app/Livewire/Donations/DonationIndex.php`) for the audit trail filters.

---

## File Structure

| File | Responsibility |
|---|---|
| `app/Livewire/Audit/VerifyIntegrity.php` + view | Recomputes and displays hash chain verification |
| `app/Console/Commands/TamperActivityLog.php` | `php artisan demo:tamper-log` — mutates one row directly, bypassing the app, for the live demo |
| `app/Livewire/Audit/ActivityLogIndex.php` + view | Filterable `activity_logs` table (user, date range, action, subject) + expandable before/after detail |
| `app/Livewire/Audit/LoginLogIndex.php` + view | Filterable `login_logs` table (user/email, date range, status) |
| `app/Services/AnomalyDetector.php` | `extremeDonations()`, `failedLoginStreaks()`, `fastApprovedDisbursements()` |
| `app/Livewire/Audit/AnomalyDashboard.php` + view | Renders the three anomaly categories |
| `database/seeders/LoginLogSeeder.php` | Seeds successful logins + a 3-in-15-minute failed streak for one user |
| `database/seeders/DisbursementSeeder.php` (modify) | One seeded disbursement approved in under a minute, to demo the fast-approval flag |
| `database/seeders/DatabaseSeeder.php` (modify) | Calls `LoginLogSeeder` |
| `routes/web.php` (modify) | Adds `/audit/integritas`, `/audit/aktivitas`, `/audit/login`, `/audit/anomali`, all `role:auditor` |
| `resources/views/components/layouts/app.blade.php` (modify) | Nav links to the 4 audit pages, shown only to Auditor |

---

## Task 1: Verifikasi Integritas page

TDD test: Auditor sees "valid" when the chain is clean; after directly corrupting one row (`forceFill()->saveQuietly()`, same technique already used in `tests/Feature/AuditLoggerTest.php`), the page reports the tampered row's id; Bendahara/Admin get 403.

```php
// tests/Feature/VerifyIntegrityTest.php
use App\Livewire\Audit\VerifyIntegrity;
use App\Models\ActivityLog;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\AuditLogger;
use Livewire\Livewire;

it('reports the chain as valid for an auditor when nothing has been tampered with', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    app(AuditLogger::class)->log('campaign.created', $auditor, null, [], ['name' => 'Test']);

    Livewire::actingAs($auditor)
        ->test(VerifyIntegrity::class)
        ->call('verify')
        ->assertSet('result.valid', true);
});

it('reports which row was tampered with', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    app(AuditLogger::class)->log('campaign.created', $auditor, null, [], ['name' => 'Test']);
    $entry = ActivityLog::first();
    $entry->forceFill(['action' => 'campaign.deleted'])->saveQuietly();

    Livewire::actingAs($auditor)
        ->test(VerifyIntegrity::class)
        ->call('verify')
        ->assertSet('result.valid', false)
        ->assertSet('result.tampered_at', $entry->id);
});

it('blocks non auditors from the integrity page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get(route('audit.integrity'))->assertForbidden();
});
```

`App\Livewire\Audit\VerifyIntegrity` (`#[Layout('components.layouts.app')]`): `public ?array $result = null;`, `verify(AuditLogger $logger)` sets `$this->result = $logger->verifyChain();`. `mount()` does not auto-run it (recomputing the whole chain is a deliberate action, matching spec's "tombol yang menghitung ulang").

View: a button "Verifikasi Sekarang"; once `$result` is set, a ledger-style panel — green/navy "Rantai log utuh, tidak ada indikasi manipulasi" when valid, red-bordered "Ketidaksesuaian terdeteksi pada baris #{{ tampered_at }}" when not.

Route: `Route::get('/audit/integritas', VerifyIntegrity::class)->middleware('role:auditor')->name('audit.integrity');`

Commit: `feat: add hash chain integrity verification page`.

---

## Task 2: `demo:tamper-log` artisan command

TDD test: running the command changes exactly one `activity_logs` row's `action` (or `after` payload) directly via the model with `saveQuietly()` (no new log entry created for the tamper itself — it must bypass the app, per spec: "sengaja mengubah satu baris log langsung di database, melewati aplikasi"), and afterwards `AuditLogger::verifyChain()` reports `valid: false`.

```php
// tests/Feature/TamperActivityLogCommandTest.php
it('tampers with the most recent activity log row and breaks the chain', function () {
    $user = \App\Models\User::factory()->create();
    app(\App\Services\AuditLogger::class)->log('campaign.created', $user, null, [], ['name' => 'Test']);
    app(\App\Services\AuditLogger::class)->log('campaign.updated', $user, null, ['name' => 'Test'], ['name' => 'Test 2']);

    expect(app(\App\Services\AuditLogger::class)->verifyChain()['valid'])->toBeTrue();

    $this->artisan('demo:tamper-log')->assertSuccessful();

    expect(app(\App\Services\AuditLogger::class)->verifyChain()['valid'])->toBeFalse();
});

it('fails gracefully when there is nothing to tamper with', function () {
    $this->artisan('demo:tamper-log')->assertFailed();
});
```

`app/Console/Commands/TamperActivityLog.php`: `protected $signature = 'demo:tamper-log';` `handle()`: `$entry = ActivityLog::query()->latest('id')->first(); if (! $entry) { $this->error('Tidak ada activity log untuk dimanipulasi.'); return self::FAILURE; } $entry->forceFill(['after' => ['tampered' => true]])->saveQuietly(); $this->info("Baris #{$entry->id} telah dimanipulasi langsung di database."); return self::SUCCESS;`

Commit: `feat: add demo:tamper-log artisan command`.

---

## Task 3: Activity log audit trail viewer

TDD test: Auditor can filter by action and date range; a non-matching filter returns no rows; clicking through renders human-readable before/after (not raw JSON) for a known change.

```php
// tests/Feature/ActivityLogViewerTest.php
it('lets an auditor filter the activity log by action', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();
    app(AuditLogger::class)->log('campaign.created', $auditor, $campaign, [], ['name' => $campaign->name]);
    app(AuditLogger::class)->log('campaign.deleted', $auditor, $campaign, ['name' => $campaign->name], []);

    Livewire::actingAs($auditor)
        ->test(ActivityLogIndex::class)
        ->set('action', 'campaign.deleted')
        ->assertSee('campaign.deleted')
        ->assertDontSee('campaign.created');
});

it('blocks non auditors from the activity log viewer', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);

    $this->actingAs($bendahara)->get(route('audit.activity'))->assertForbidden();
});
```

`App\Livewire\Audit\ActivityLogIndex`: `#[Url] public string $action = '';`, `#[Url] public ?string $user_id = '';`, `#[Url] public ?string $from = '';`, `#[Url] public ?string $to = '';`, `public ?int $expandedId = null;` with a `toggle(int $id)` method. `render()` builds `ActivityLog::query()->with('user')->latest()`, conditionally filtering on each set property (`when()` chains), paginates.

View: filter bar (action text input, user select, two date inputs), table (time, user, action, subject), each row togglable to show a simple `<dl>` of before→after key/value pairs (iterate `array_keys($entry->before + $entry->after)`, skip pairs that are equal) instead of dumping raw JSON.

Route: `Route::get('/audit/aktivitas', ActivityLogIndex::class)->middleware('role:auditor')->name('audit.activity');`

Commit: `feat: add activity log audit trail viewer`.

---

## Task 4: Login log viewer

TDD test: Auditor sees both success and failed rows, can filter by status.

```php
// tests/Feature/LoginLogViewerTest.php
it('lets an auditor filter login logs by status', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    LoginLog::factory()->create(['email' => 'a@test.com', 'status' => 'success']);
    LoginLog::factory()->create(['email' => 'b@test.com', 'status' => 'failed']);

    Livewire::actingAs($auditor)
        ->test(LoginLogIndex::class)
        ->set('status', 'failed')
        ->assertSee('b@test.com')
        ->assertDontSee('a@test.com');
});
```

Needs a `LoginLogFactory` (doesn't exist yet — `LoginLog` was created in Phase 1 without one since it's populated only via the auth listeners). Create `database/factories/LoginLogFactory.php`: `email`, `ip_address` (`fake()->ipv4()`), `user_agent`, `status` default `'success'`.

`App\Livewire\Audit\LoginLogIndex`: same `#[Url]` filter shape (`status`, `email`, `from`, `to`), simple table.

Route: `Route::get('/audit/login', LoginLogIndex::class)->middleware('role:auditor')->name('audit.login');`

Commit: `feat: add login log viewer`.

---

## Task 5: Anomaly detector service + dashboard

TDD tests directly on the service (no HTTP/Livewire needed for the detection logic itself):

```php
// tests/Feature/AnomalyDetectorTest.php
it('flags a donation far above its campaign average', function () {
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->count(5)->create(['amount' => 100_000]);
    $outlier = Donation::factory()->for($campaign)->create(['amount' => 50_000_000]);

    $flagged = app(AnomalyDetector::class)->extremeDonations();

    expect($flagged->pluck('id'))->toContain($outlier->id);
});

it('flags three or more failed logins within 15 minutes for the same email', function () {
    $base = now();
    LoginLog::factory()->create(['email' => 'x@test.com', 'status' => 'failed', 'created_at' => $base]);
    LoginLog::factory()->create(['email' => 'x@test.com', 'status' => 'failed', 'created_at' => $base->copy()->addMinutes(5)]);
    LoginLog::factory()->create(['email' => 'x@test.com', 'status' => 'failed', 'created_at' => $base->copy()->addMinutes(10)]);

    $flagged = app(AnomalyDetector::class)->failedLoginStreaks();

    expect($flagged->pluck('email'))->toContain('x@test.com');
});

it('does not flag a failed login streak broken by a success or spread past 15 minutes', function () {
    $base = now();
    LoginLog::factory()->create(['email' => 'y@test.com', 'status' => 'failed', 'created_at' => $base]);
    LoginLog::factory()->create(['email' => 'y@test.com', 'status' => 'success', 'created_at' => $base->copy()->addMinutes(2)]);
    LoginLog::factory()->create(['email' => 'y@test.com', 'status' => 'failed', 'created_at' => $base->copy()->addMinutes(4)]);
    LoginLog::factory()->create(['email' => 'y@test.com', 'status' => 'failed', 'created_at' => $base->copy()->addMinutes(6)]);

    $flagged = app(AnomalyDetector::class)->failedLoginStreaks();

    expect($flagged->pluck('email'))->not->toContain('y@test.com');
});

it('flags a disbursement approved less than a minute after submission', function () {
    $fast = Disbursement::factory()->create([
        'status' => DisbursementStatus::Approved,
        'created_at' => now(),
        'reviewed_at' => now()->addSeconds(30),
    ]);
    Disbursement::factory()->create([
        'status' => DisbursementStatus::Approved,
        'created_at' => now(),
        'reviewed_at' => now()->addMinutes(10),
    ]);

    $flagged = app(AnomalyDetector::class)->fastApprovedDisbursements();

    expect($flagged->pluck('id'))->toContain($fast->id);
});
```

`app/Services/AnomalyDetector.php`:
- `extremeDonations(): Collection` — group all donations by `campaign_id` (load via `Donation::all()->groupBy('campaign_id')`), for each group with 2+ donations compute mean and population standard deviation in PHP, return the flattened collection of donations whose `amount > mean + 2 * stddev`.
- `failedLoginStreaks(): Collection` — `LoginLog::query()->orderBy('email')->orderBy('created_at')->get()->groupBy('email')`, walk each group resetting a streak counter on any `success` row, and whenever 3+ consecutive `failed` rows accumulate, check `$streak->last()->created_at->diffInMinutes($streak->first()->created_at) <= 15`; return one representative row per flagged email (e.g. the last failed attempt) so the view can list `email` + `attempt count` + `window`.
- `fastApprovedDisbursements(): Collection` — `Disbursement::query()->where('status', DisbursementStatus::Approved)->whereNotNull('reviewed_at')->get()->filter(fn ($d) => $d->created_at->diffInSeconds($d->reviewed_at) < 60)`.

`App\Livewire\Audit\AnomalyDashboard` (`#[Layout('components.layouts.app')]`): `render()` calls all three detector methods, passes to the view. View: three sections styled like flagged report entries (red-bordered rows), each explaining why it's flagged (e.g. "Rp 50.000.000 — 12x rata rata program").

Route: `Route::get('/audit/anomali', AnomalyDashboard::class)->middleware('role:auditor')->name('audit.anomalies');`

Commit: `feat: add anomaly detector service and dashboard`.

---

## Task 6: Seed anomaly-triggering data + nav links

- `database/seeders/LoginLogSeeder.php`: a handful of successful `login_logs` rows for each seeded user (spread over recent days), plus one email with 3 failed attempts within a 10-minute window (no success in between) to guarantee the anomaly dashboard has something to show. Add to `DatabaseSeeder::run()`'s `$this->call([...])` list.
- `database/seeders/DisbursementSeeder.php`: adjust so at least one seeded Approved disbursement has `reviewed_at` within 60 seconds of `created_at` (currently always 30–2000 minutes later), so the fast-approval flag has a real example without waiting for a live demo.
- Nav: in `resources/views/components/layouts/app.blade.php`, add `@if(auth()->user()->isAuditor())` block with links to the 4 new routes (Verifikasi Integritas, Log Aktivitas, Log Login, Dashboard Anomali).

Verify: `php artisan migrate:fresh --seed`, then log in as `auditor1@sidona.test` and confirm `/audit/anomali` shows at least one flagged donation, one flagged login streak, and (if the disbursement tweak landed) one fast-approval flag.

Commit: `feat: seed anomaly-triggering data and add auditor nav links`.

---

## Manual verification checklist

1. `npm run build`, `php artisan serve`, log in as `auditor1@sidona.test`.
2. `/audit/integritas` → click "Verifikasi Sekarang" → chain reports valid.
3. In a second terminal: `php artisan demo:tamper-log`, then reload `/audit/integritas` and click verify again → reports the tampered row's id.
4. `/audit/aktivitas` → filter by action `campaign.created` → only matching rows shown; click a row → see readable before/after, not JSON.
5. `/audit/login` → filter by status `failed` → only failed attempts shown.
6. `/audit/anomali` → see at least one extreme-donation flag, one failed-login-streak flag, one fast-approval flag (from seeded data).
7. Log in as `admin@sidona.test` or `bendahara1@sidona.test` → none of the 4 `/audit/...` routes are reachable (403), and the nav links aren't shown.
8. Run `php artisan test` → all tests (Phase 1–4) pass.
9. Run `php artisan migrate:fresh --seed` once more afterward so the tampered row from step 3 doesn't linger in the dev database.
