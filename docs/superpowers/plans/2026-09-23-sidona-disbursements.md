# SIDONA Disbursement Transactions (Phase 3 of 5) Implementation Plan

**Context:** Phase 1 (foundation: auth, roles, hash-chained `AuditLogger`, Campaign CRUD) and Phase 2 (donation transactions: `Donation` model, guest submission, Bendahara verify/reject) are both done on branch `phase-1-foundation`. This phase implements ketentuan wajib #4 (`disbursements` is the 3rd of the 3 required tabel utama) and #5/#6 (Transaksi 2: penyaluran dana, spec §5) — the segregation-of-duties centerpiece of the whole system: Bendahara proposes (maker), Admin approves (checker), and a program's spendable balance is enforced with a locked DB transaction to prevent race conditions (spec §5, Transaksi 2, point 2).

**Scope:** Only "Transaksi 2: Penyaluran dana" (spec §5). No new dependencies.

**Reused from Phase 1/2:** `AuditLogger` (`app/Services/AuditLogger.php`) for hash-chained logging: `log(string $action, ?User $user, ?Model $subject, array $before, array $after)`. `EnsureUserHasRole` middleware alias `role:...` (`bootstrap/app.php`) for route-level gates. The Donation review pattern (`app/Livewire/Donations/DonationIndex.php`, `tests/Feature/DonationReviewTest.php`) — same `startReject`/`confirmReject` inline-form shape — is the template for the approve/reject inbox here. `CampaignDetail::mount()`'s `abort_unless(...)` pattern (`app/Livewire/Public/CampaignDetail.php`) is the template for gating the submission form.

---

## File Structure

| File | Responsibility |
|---|---|
| `app/Enums/DisbursementStatus.php` | Backed enum: submitted/approved/rejected |
| `app/Models/Disbursement.php` | Disbursement model |
| `app/Models/Campaign.php` (modify) | Adds `verifiedDonationsTotal()`, `approvedDisbursementsTotal()`, `availableBalance()` |
| `database/migrations/*_create_disbursements_table.php` | `disbursements` schema |
| `database/factories/DisbursementFactory.php` | Test factory |
| `app/Policies/DisbursementPolicy.php` | `create` → Bendahara only; `approve`/`reject` → Admin only, on `submitted` status, and never the same user who submitted it |
| `app/Livewire/Disbursements/DisbursementForm.php` + view | Bendahara: propose a disbursement for one campaign, balance-checked under a locked transaction |
| `app/Livewire/Disbursements/DisbursementIndex.php` + view | Internal: list + Admin approve/reject (mirrors `DonationIndex`) |
| `routes/web.php` (modify) | Adds `/campaigns/{campaign}/penyaluran/ajukan` (role: bendahara) and `/penyaluran` (auth) |
| `resources/views/livewire/campaigns/campaign-index.blade.php` (modify) | Adds an "Ajukan Penyaluran" link per row, visible only `@can('create', Disbursement::class)` |
| `resources/views/components/layouts/app.blade.php` (modify) | Nav link to `/penyaluran` |
| `database/seeders/DisbursementSeeder.php` | 10–15 disbursements, mixed status, amounts kept under each campaign's available balance |
| `database/seeders/DatabaseSeeder.php` (modify) | Calls `DisbursementSeeder` |

---

## Task 1: Campaign balance methods + Disbursement model/migration/enum/factory

TDD test asserts the balance formula (spec §4: "Saldo program dihitung on the fly — donasi terverifikasi dikurangi penyaluran disetujui"):

```php
// tests/Feature/CampaignBalanceTest.php
use App\Enums\DonationStatus;
use App\Enums\DisbursementStatus;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;

it('computes available balance as verified donations minus approved disbursements', function () {
    $campaign = Campaign::factory()->create();

    Donation::factory()->for($campaign)->create(['amount' => 1_000_000, 'status' => DonationStatus::Verified]);
    Donation::factory()->for($campaign)->create(['amount' => 500_000, 'status' => DonationStatus::Verified]);
    Donation::factory()->for($campaign)->create(['amount' => 9_000_000, 'status' => DonationStatus::Pending]);
    Donation::factory()->for($campaign)->create(['amount' => 9_000_000, 'status' => DonationStatus::Rejected]);

    Disbursement::factory()->for($campaign)->create(['amount' => 300_000, 'status' => DisbursementStatus::Approved]);
    Disbursement::factory()->for($campaign)->create(['amount' => 9_000_000, 'status' => DisbursementStatus::Submitted]);
    Disbursement::factory()->for($campaign)->create(['amount' => 9_000_000, 'status' => DisbursementStatus::Rejected]);

    expect($campaign->availableBalance())->toBe(1_200_000);
});
```

`Campaign` model additions:
```php
public function verifiedDonationsTotal(): int
{
    return (int) $this->donations()->where('status', DonationStatus::Verified)->sum('amount');
}

public function approvedDisbursementsTotal(): int
{
    return (int) $this->disbursements()->where('status', DisbursementStatus::Approved)->sum('amount');
}

public function availableBalance(): int
{
    return $this->verifiedDonationsTotal() - $this->approvedDisbursementsTotal();
}
```
Plus `donations(): HasMany` / `disbursements(): HasMany` relations (Donation already has `campaign(): BelongsTo` from Phase 2, add the inverse here).

Migration `disbursements`: `id`, `foreignId('campaign_id')->constrained()->cascadeOnDelete()`, `unsignedBigInteger('amount')`, `text('description')`, `string('status')->default('submitted')`, `foreignId('submitted_by')->constrained('users')->cascadeOnDelete()`, `foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()`, `timestamp('reviewed_at')->nullable()`, `string('rejection_reason')->nullable()`, `timestamps()`.

`DisbursementStatus` enum: `Submitted='submitted'`, `Approved='approved'`, `Rejected='rejected'`, `label()`.

`Disbursement` model: `$fillable` = campaign_id, amount, description, status, submitted_by, reviewed_by, reviewed_at, rejection_reason; `casts()` → status: DisbursementStatus::class, reviewed_at: datetime; relations `campaign()`, `submitter()` (User via submitted_by), `reviewer()` (User via reviewed_by).

Factory: campaign via `Campaign::factory()`, amount `fake()->numberBetween(50_000, 500_000)`, description a sentence, status default Submitted, submitted_by via `User::factory()->bendahara()` — reuse the existing `UserFactory` (no state exists yet for role; simplest is `User::factory()->create(['role' => UserRole::Bendahara])` inline in the factory's `definition()`).

Commit: `feat: add Disbursement model and Campaign balance calculation`.

---

## Task 2: DisbursementPolicy

TDD test: only Bendahara can `create`; only Admin can `approve`/`reject` a `submitted` disbursement; an Admin cannot approve one they submitted themselves (defensive segregation-of-duties check per spec §3, even though role exclusivity already prevents a Bendahara-turned-Admin scenario today).

```php
public function create(User $user): bool
{
    return $user->role === UserRole::Bendahara;
}

public function approve(User $user, Disbursement $disbursement): bool
{
    return $user->role === UserRole::Admin
        && $disbursement->status === DisbursementStatus::Submitted
        && $disbursement->submitted_by !== $user->id;
}

public function reject(User $user, Disbursement $disbursement): bool
{
    return $this->approve($user, $disbursement);
}
```

Commit: `feat: add DisbursementPolicy`.

---

## Task 3: Disbursement submission form (Bendahara)

TDD tests:

```php
// tests/Feature/DisbursementSubmissionTest.php
use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Disbursements\DisbursementForm;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\User;
use Livewire\Livewire;

it('lets a bendahara submit a disbursement within the available balance', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 1_000_000, 'status' => DonationStatus::Verified]);

    Livewire::actingAs($bendahara)
        ->test(DisbursementForm::class, ['campaign' => $campaign])
        ->set('amount', 500_000)
        ->set('description', 'Pembelian tenda pengungsian')
        ->call('submit')
        ->assertRedirect(route('disbursements.index'));

    $disbursement = Disbursement::first();
    expect($disbursement->campaign_id)->toBe($campaign->id);
    expect($disbursement->submitted_by)->toBe($bendahara->id);
    expect(ActivityLog::where('action', 'disbursement.submitted')->where('subject_id', $disbursement->id)->exists())->toBeTrue();
});

it('rejects a disbursement that exceeds the available balance', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['amount' => 100_000, 'status' => DonationStatus::Verified]);

    Livewire::actingAs($bendahara)
        ->test(DisbursementForm::class, ['campaign' => $campaign])
        ->set('amount', 500_000)
        ->set('description', 'Pembelian tenda')
        ->call('submit')
        ->assertHasErrors('amount');

    expect(Disbursement::count())->toBe(0);
});

it('refuses to load the form for a campaign with no available balance', function () {
    $bendahara = User::factory()->create(['role' => UserRole::Bendahara]);
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($bendahara)
        ->test(DisbursementForm::class, ['campaign' => $campaign])
        ->assertForbidden();
});

it('blocks an admin from opening the disbursement creation route', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    $this->actingAs($admin)->get(route('disbursements.create', $campaign))->assertForbidden();
});
```

`App\Livewire\Disbursements\DisbursementForm` (`#[Layout('components.layouts.app')]`):
- `public Campaign $campaign; public int $amount = 0; public string $description = '';`
- `mount(Campaign $campaign)`: `Gate::authorize('create', Disbursement::class); abort_unless($campaign->availableBalance() > 0, 403); $this->campaign = $campaign;`
- `rules()`: amount required|integer|min:1, description required|string|max:1000
- `submit(AuditLogger $logger)`: validate; `DB::transaction(function () use (...) { $campaign = Campaign::query()->lockForUpdate()->findOrFail($this->campaign->id); if ($this->amount > $campaign->availableBalance()) { throw ValidationException::withMessages(['amount' => 'Jumlah melebihi saldo program yang tersedia.']); } $disbursement = Disbursement::create([...'submitted_by' => auth()->id(), 'status' => DisbursementStatus::Submitted]); $logger->log('disbursement.submitted', auth()->user(), $disbursement, [], $disbursement->only([...])); });` then `$this->redirectRoute('disbursements.index', navigate: true);`

Route: `Route::get('/campaigns/{campaign}/penyaluran/ajukan', DisbursementForm::class)->middleware('role:bendahara')->name('disbursements.create');` inside the `auth` group.

Commit: `feat: add disbursement submission form with balance-locked validation`.

---

## Task 4: Disbursement review inbox (Admin approve/reject)

TDD tests (same shape as `tests/Feature/DonationReviewTest.php`):

```php
// tests/Feature/DisbursementReviewTest.php
- 'lets an admin approve a submitted disbursement and logs it' (status → Approved, reviewed_by → admin id, ActivityLog 'disbursement.approved')
- 'lets an admin reject a submitted disbursement with a reason' (startReject/confirmReject, status → Rejected, rejection_reason stored, ActivityLog 'disbursement.rejected')
- 'requires a reason to reject a disbursement' (assertHasErrors('rejectionReason'))
- 'prevents a bendahara from approving a disbursement' (assertForbidden)
- 'prevents an admin from approving a disbursement they submitted themselves' (assertForbidden — segregation of duties)
- 'prevents approving a disbursement that is no longer submitted' (assertForbidden)
```

`App\Livewire\Disbursements\DisbursementIndex` — same shape as `App\Livewire\Donations\DonationIndex` (`app/Livewire/Donations/DonationIndex.php`): `#[Url] public string $status = 'submitted';`, `approve(int $id, AuditLogger $logger)`, `startReject(int $id)`, `cancelReject()`, `confirmReject(AuditLogger $logger)` (validates `rejectionReason` required|string|min:3), `render()` paginates `Disbursement::with(['campaign','submitter'])->latest()`.

View: table (campaign, amount, description, diajukan oleh, status, aksi), reusing the same inline reject-form pattern as `resources/views/livewire/donations/donation-index.blade.php`.

Route: `Route::get('/penyaluran', DisbursementIndex::class)->name('disbursements.index');` inside the `auth` group (all 3 roles view; policy gates the actions).

Add nav link in `resources/views/components/layouts/app.blade.php` next to "Donasi": `<a href="{{ route('disbursements.index') }}" wire:navigate>Penyaluran</a>`.

Add an "Ajukan Penyaluran" link in `resources/views/livewire/campaigns/campaign-index.blade.php` per row, shown only `@can('create', App\Models\Disbursement::class)`.

Commit: `feat: add disbursement review inbox for admin`.

---

## Task 5: Seed disbursement data

`database/seeders/DisbursementSeeder.php`: for each campaign with `availableBalance() > 0`, create 2–4 disbursements using `Disbursement::factory()` with `submitted_by` a random seeded Bendahara, amounts kept safely under the remaining balance as each one is added (recompute `availableBalance()` after each Approved one so seeded data never goes negative) — mix of ~50% Approved (reviewed_by a random seeded Admin), ~30% Submitted, ~20% Rejected (with a reason). Target 10–15 total across all campaigns (spec §9). Log every submission (`disbursement.submitted`) and every review transition (`disbursement.approved`/`disbursement.rejected`) via `AuditLogger`, same as `DonationSeeder` does.

Add `DisbursementSeeder::class` to `DatabaseSeeder::run()`'s `$this->call([...])` list, after `DonationSeeder`.

Verify: `php artisan migrate:fresh --seed`, then confirm the hash chain is still valid and no campaign's `availableBalance()` went negative.

Commit: `feat: seed disbursement data`.

---

## Manual verification checklist

1. `npm run build`, `php artisan serve`.
2. Log in as `bendahara1@sidona.test` → on `/campaigns`, click "Ajukan Penyaluran" on a campaign with available balance → submit an amount within balance → lands on `/penyaluran`, new row shows "Diajukan".
3. Try submitting an amount above the available balance → validation error, nothing created.
4. Visit `/campaigns/{id}/penyaluran/ajukan` for a campaign with zero balance directly → 403.
5. Log in as `admin@sidona.test` → `/penyaluran` → approve the pending one → status changes, `activity_logs` gets a `disbursement.approved` row with the admin as `reviewed_by` and the bendahara as `submitted_by`.
6. Reject another submitted disbursement with a reason → status changes, reason stored.
7. Log in as `auditor1@sidona.test` → `/penyaluran` → list visible, no Approve/Reject buttons.
8. Run `php artisan test` → all tests (Phase 1 + 2 + 3) pass.
