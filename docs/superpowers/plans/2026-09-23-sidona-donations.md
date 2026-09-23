# SIDONA Donation Transactions (Phase 2 of 5) Implementation Plan

**Context:** Phase 1 (foundation) is done and merged into branch `phase-1-foundation`: auth, 3 roles (Bendahara/Admin/Auditor), the hash-chained `AuditLogger`, and internal Campaign CRUD. This phase implements ketentuan wajib #4 (`donations` table), #5 (Transaksi 1: donasi masuk) and #6 (donation-specific validation) from the design spec. It also opens the first guest-facing surface: donors need no login (spec §3), so this phase adds public routes alongside the existing authenticated `/campaigns` area.

**Scope:** Only "Transaksi 1: Donasi masuk" (spec §5, Transaksi 1). Disbursement (Transaksi 2) is Phase 3.

**Tech stack notes learned in Phase 1** (carry forward): Laravel 13.32, Livewire 3.8.9 (pinned), Pest 4.7 with `tests/Pest.php` using `pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature')`, SQLite dev DB. New non-`User` models use plain `protected $fillable = [...]` (not the `#[Fillable]` attribute — that's only on the scaffold-generated `User` model). Run `vendor/bin/pint --dirty --format agent` after PHP edits, before each commit.

**Branch:** continue on `phase-1-foundation` (confirmed with user).

---

## File Structure

| File | Responsibility |
|---|---|
| `app/Enums/DonationStatus.php` | Backed enum: pending/verified/rejected |
| `app/Models/Donation.php` | Donation model, auto-generates `reference_code` |
| `database/migrations/*_create_donations_table.php` | `donations` schema |
| `database/factories/DonationFactory.php` | Test factory |
| `app/Policies/DonationPolicy.php` | `viewAny` open to all internal roles; `verify`/`reject` restricted to Bendahara on pending donations |
| `app/Livewire/Public/CampaignList.php` + view | Guest: list active campaigns |
| `app/Livewire/Public/CampaignDetail.php` + view | Guest: campaign detail + donation form (`WithFileUploads`) |
| `app/Livewire/Public/DonationStatusCheck.php` + view | Guest: look up donation by reference code |
| `app/Livewire/Donations/DonationIndex.php` + view | Internal: pending/verified/rejected list, verify/reject actions (Bendahara only) |
| `resources/views/components/layouts/public.blade.php` | Shared layout for guest pages (nav back to `/login`) |
| `routes/web.php` (modify) | Adds `/program`, `/program/{campaign}`, `/donasi/cek`, `/donasi`; changes `/` to route guests to `/program` |
| `database/seeders/DonationSeeder.php` | 40–60 donations, mixed status, a few extreme amounts |
| `database/seeders/DatabaseSeeder.php` (modify) | Calls `DonationSeeder` |
| `resources/views/components/layouts/app.blade.php` (modify) | Nav link to `/donasi` |

---

## Task 1: Donation model, migration, enum, factory

TDD test asserts: enum cast works, `reference_code` is auto-generated and unique, `campaign()`/`verifier()` relations resolve.

```php
// tests/Feature/DonationModelTest.php
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Donation;

it('auto generates a unique reference code on create', function () {
    $donation = Donation::factory()->create();

    expect($donation->reference_code)->not->toBeEmpty();
    expect(Donation::where('reference_code', $donation->reference_code)->count())->toBe(1);
});

it('casts status to the DonationStatus enum and defaults to pending', function () {
    $donation = Donation::factory()->create();

    expect($donation->fresh()->status)->toBe(DonationStatus::Pending);
});

it('belongs to a campaign', function () {
    $campaign = Campaign::factory()->create();
    $donation = Donation::factory()->for($campaign)->create();

    expect($donation->campaign->id)->toBe($campaign->id);
});
```

Migration `donations`: `id`, `foreignId('campaign_id')->constrained()->cascadeOnDelete()`, `string('reference_code', 20)->unique()`, `string('donor_name')`, `string('donor_contact')`, `unsignedBigInteger('amount')`, `string('proof_path')`, `string('status')->default('pending')`, `foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()`, `timestamp('verified_at')->nullable()`, `string('rejection_reason')->nullable()`, `timestamps()`.

`DonationStatus` enum: `Pending='pending'`, `Verified='verified'`, `Rejected='rejected'`, `label()`.

`Donation` model: `$fillable` = campaign_id, donor_name, donor_contact, amount, proof_path, status, verified_by, verified_at, rejection_reason; `casts()` → status: DonationStatus::class, verified_at: datetime; relations `campaign(): BelongsTo` and `verifier(): BelongsTo` (User via `verified_by`); `protected static function booted()` registers a `creating` listener that sets `reference_code` via a static `generateReferenceCode()` (`'DON-'.strtoupper(Str::random(8))`, looped until unique) when empty.

Factory: campaign_id via `Campaign::factory()`, donor_name/contact via faker, amount `fake()->numberBetween(10_000, 5_000_000)`, proof_path a fake string path, status default Pending.

Commit: `feat: add Donation model with auto-generated reference codes`.

---

## Task 2: DonationPolicy

TDD test: Bendahara can verify/reject a pending donation, Admin/Auditor cannot; nobody can verify an already-verified donation; `viewAny` true for all three internal roles.

`app/Policies/DonationPolicy.php`:
```php
public function viewAny(User $user): bool { return true; }

public function verify(User $user, Donation $donation): bool
{
    return $user->role === UserRole::Bendahara && $donation->status === DonationStatus::Pending;
}

public function reject(User $user, Donation $donation): bool
{
    return $user->role === UserRole::Bendahara && $donation->status === DonationStatus::Pending;
}
```

Commit: `feat: add DonationPolicy`.

---

## Task 3: Public campaign listing (guest)

TDD test: guest (no auth) can GET `/program` and sees only Active campaigns, not Completed ones.

`resources/views/components/layouts/public.blade.php`: same shell as `guest.blade.php` but with a simple nav (`SIDONA` brand + link to `/login` for staff).

`App\Livewire\Public\CampaignList`: no auth required, `render()` returns `Campaign::query()->where('status', CampaignStatus::Active)->latest()->paginate(10)`.

Route: `Route::get('/program', CampaignList::class)->name('program.index');` — outside the `auth`/`guest` middleware groups (public).

Commit: `feat: add public campaign listing page`.

---

## Task 4: Public campaign detail + donation form

This is the core of Transaksi 1. TDD tests (write these first, confirm RED, then implement):

```php
// tests/Feature/DonationSubmissionTest.php
use App\Enums\CampaignStatus;
use App\Enums\DonationStatus;
use App\Livewire\Public\CampaignDetail;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('lets a guest submit a donation with valid data and a proof file', function () {
    Storage::fake('public');
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('donor_name', 'Budi Santoso')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->set('proof', UploadedFile::fake()->image('bukti.jpg')->size(500))
        ->call('submit')
        ->assertHasNoErrors();

    $donation = Donation::first();
    expect($donation->campaign_id)->toBe($campaign->id);
    expect($donation->status)->toBe(DonationStatus::Pending);
    Storage::disk('public')->assertExists($donation->proof_path);
});

it('rejects a donation below the minimum amount', function () {
    Storage::fake('public');
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 5000)
        ->set('proof', UploadedFile::fake()->image('bukti.jpg'))
        ->call('submit')
        ->assertHasErrors('amount');

    expect(Donation::count())->toBe(0);
});

it('rejects a proof file that is not jpg, png or pdf', function () {
    Storage::fake('public');
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->set('proof', UploadedFile::fake()->create('bukti.txt', 100))
        ->call('submit')
        ->assertHasErrors('proof');
});

it('refuses to load the donation form for a completed campaign', function () {
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Completed]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->assertForbidden();
});

it('writes an activity log entry for a submitted donation without a user', function () {
    Storage::fake('public');
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Active, 'ends_on' => now()->addDays(10)]);

    Livewire::test(CampaignDetail::class, ['campaign' => $campaign])
        ->set('donor_name', 'Budi')
        ->set('donor_contact', '08123456789')
        ->set('amount', 50000)
        ->set('proof', UploadedFile::fake()->image('bukti.jpg'))
        ->call('submit');

    $donation = Donation::first();
    expect(\App\Models\ActivityLog::where('action', 'donation.created')
        ->where('subject_id', $donation->id)
        ->whereNull('user_id')
        ->exists())->toBeTrue();
});
```

`App\Livewire\Public\CampaignDetail` (`use Livewire\WithFileUploads;`):
- `public Campaign $campaign;`
- `public string $donor_name = ''; public string $donor_contact = ''; public int $amount = 0; public $proof;`
- `mount(Campaign $campaign)`: `abort_unless($campaign->status === CampaignStatus::Active && ! $campaign->ends_on->isPast(), 403);`
- `rules()`: donor_name required|string|max:255, donor_contact required|string|max:255, amount required|integer|min:10000, proof required|file|mimes:jpg,jpeg,png,pdf|max:2048
- `submit(AuditLogger $logger)`: validate; `$path = $this->proof->store('donation-proofs', 'public');`; create Donation with status Pending; `$logger->log('donation.created', null, $donation, [], $donation->only([...]));`; `session()->flash('reference_code', $donation->reference_code);`; reset form fields.
- View shows campaign detail + form; after successful submit (check `session('reference_code')`), show a confirmation panel with the reference code instead of re-showing the form (use `wire:key`/conditional Blade, no redirect needed since this is a single-page flow).

Route: `Route::get('/program/{campaign}', CampaignDetail::class)->name('program.show');` (public).

Commit: `feat: add public donation submission form`.

---

## Task 5: Guest donation status check

TDD test: submitting a known reference code shows the donation's status and campaign name; an unknown code shows a "not found" message instead of erroring.

`App\Livewire\Public\DonationStatusCheck`: `public string $reference_code = ''; public ?Donation $result = null; public bool $searched = false;` `check()`: validate reference_code required; `$this->result = Donation::with('campaign')->where('reference_code', $this->reference_code)->first(); $this->searched = true;`

Route: `Route::get('/donasi/cek', DonationStatusCheck::class)->name('donations.check');` (public).

Commit: `feat: add guest donation status check`.

---

## Task 6: Internal donation review inbox (Bendahara verify/reject)

TDD tests:

```php
// tests/Feature/DonationReviewTest.php
use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Donations\DonationIndex;
use App\Models\ActivityLog;
use App\Models\Donation;
use App\Models\User;
use Livewire\Livewire;

it('lets a bendahara verify a pending donation and logs it', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('verify', $donation->id);

    $donation->refresh();
    expect($donation->status)->toBe(DonationStatus::Verified);
    expect($donation->verified_by)->toBe($bendahara->id);
    expect(ActivityLog::where('action', 'donation.verified')->where('subject_id', $donation->id)->exists())->toBeTrue();
});

it('lets a bendahara reject a pending donation with a reason', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('startReject', $donation->id)
        ->set('rejectionReason', 'Bukti transfer tidak terbaca')
        ->call('confirmReject');

    $donation->refresh();
    expect($donation->status)->toBe(DonationStatus::Rejected);
    expect($donation->rejection_reason)->toBe('Bukti transfer tidak terbaca');
    expect(ActivityLog::where('action', 'donation.rejected')->where('subject_id', $donation->id)->exists())->toBeTrue();
});

it('requires a reason to reject a donation', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('startReject', $donation->id)
        ->set('rejectionReason', '')
        ->call('confirmReject')
        ->assertHasErrors('rejectionReason');

    expect($donation->fresh()->status)->toBe(DonationStatus::Pending);
});

it('prevents an admin from verifying a donation', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Pending]);

    Livewire::actingAs($admin)
        ->test(DonationIndex::class)
        ->call('verify', $donation->id)
        ->assertForbidden();

    expect($donation->fresh()->status)->toBe(DonationStatus::Pending);
});

it('prevents verifying a donation that is already verified', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $donation = Donation::factory()->create(['status' => DonationStatus::Verified]);

    Livewire::actingAs($bendahara)
        ->test(DonationIndex::class)
        ->call('verify', $donation->id)
        ->assertForbidden();
});
```

`App\Livewire\Donations\DonationIndex` (`#[Layout('components.layouts.app')]`):
- `render()`: `Donation::query()->with('campaign')->latest()->paginate(15)` (default view shows all; a simple status filter can be a query-string-backed public property, e.g. `#[Url] public string $status = 'pending';` filtering when set — keep simple, filter only if `$status !== 'all'`).
- `verify(Donation $donation, AuditLogger $logger)`: `Gate::authorize('verify', $donation);` capture `$before`, update status→Verified, verified_by→auth id, verified_at→now(); log `donation.verified`; flash.
- `startReject(int $donationId)`: sets `$this->rejectingId` + resets `$this->rejectionReason`.
- `confirmReject(AuditLogger $logger)`: load donation by `$this->rejectingId`; `Gate::authorize('reject', $donation)`; `$this->validate(['rejectionReason' => ['required','string','min:3']])`; update status→Rejected + verified_by/verified_at/rejection_reason; log `donation.rejected`; clear `$this->rejectingId`; flash.

View: table of donations (reference code, campaign, donor, amount, status badge, link to proof via `Storage::url()`), verify/reject buttons shown only `@can('verify', $donation)` / `@can('reject', $donation)`, inline reject form (textarea + confirm/cancel) shown when `$rejectingId === $donation->id`.

Route: `Route::get('/donasi', DonationIndex::class)->middleware('auth')->name('donations.index');` — no `role:` restriction (all 3 internal roles can view; the policy gates the actions).

Add nav link in `resources/views/components/layouts/app.blade.php`: `<a href="{{ route('donations.index') }}" wire:navigate>Donasi</a>` next to the existing "Program Donasi" link.

Commit: `feat: add donation review inbox for bendahara`.

---

## Task 7: Public homepage + storage link

- Change root route: `Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('program.index'));`
- Run `php artisan storage:link` (idempotent; `/public/storage` is already gitignored from the Laravel default `.gitignore`, confirmed in Phase 1).
- Manual check: visiting `/` as a guest lands on `/program`.

Commit: `feat: route guests to the public campaign listing`.

---

## Task 8: Seed donation data

`database/seeders/DonationSeeder.php`: for each seeded campaign, create a spread of donations (40–60 total across all campaigns) using `Donation::factory()`, with:
- ~60% `Verified` (set `verified_by` to a random seeded Bendahara, `verified_at` shortly after `created_at`)
- ~25% `Pending`
- ~15% `Rejected` (with a `rejection_reason`)
- 2–3 donations with an amount far above the others in their campaign (e.g. 20–50x a normal donation) to seed future anomaly-detection data (spec §9, §6.3 — the flagging logic itself is Phase 4, this just seeds the raw data)
- Each creation logged via `AuditLogger->log('donation.created', ...)`, and verify/reject transitions also logged (`donation.verified`/`donation.rejected`), matching the real flow's audit trail.

Add `DonationSeeder::class` to `DatabaseSeeder::run()`'s `$this->call([...])` list, after `CampaignSeeder`.

Verify: `php artisan migrate:fresh --seed`, then `php artisan tinker --execute='dd(app(App\Services\AuditLogger::class)->verifyChain());'` → still `valid: true`.

Commit: `feat: seed donation data`.

---

## Manual verification checklist

1. `npm run build`, `php artisan serve`.
2. As a guest (no login), visit `/` → lands on `/program`, only Active campaigns listed.
3. Click a campaign → detail page with donation form. Submit with a fake image as proof → see a reference code confirmation.
4. Visit `/donasi/cek`, enter that reference code → see status "Pending".
5. Log in as `bendahara1@sidona.test` → visit `/donasi` → see the new donation, click Verify → status changes, `activity_logs` gets a `donation.verified` row.
6. Create another donation as guest, log in as bendahara, reject it with a reason → status changes, reason stored.
7. Log in as `admin@sidona.test` → visit `/donasi` → donation list visible but no Verify/Reject buttons.
8. Run `php artisan test` → all tests (Phase 1 + Phase 2) pass.
