# SIDONA PDF Reports with Checksum (Phase 5 of 5) Implementation Plan

**Context:** Phases 1–4 are done on branch `phase-1-foundation`: foundation/auth, donation transactions, disbursement transactions, and the audit features (hash chain verification, audit trail viewers, anomaly dashboard). This final phase implements spec §6.2 (PDF export with checksum) and §7 (Laporan): donation/disbursement/balance reports as PDF, and a "Cek Keaslian Laporan" page where an Auditor re-uploads a PDF to prove it hasn't been altered since export.

**Scope:** Spec §6.2 + §7. This closes out ketentuan wajib #7 (Laporan) and the last piece of #9 (report_exports table, listed in spec §4 as a supporting table).

**The checksum design (read before Task 1):** Spec says "Sistem menghitung SHA256 dari isi PDF, checksum ditampilkan di footer PDF... Auditor dapat mengunggah ulang PDF untuk memverifikasi checksum masih cocok." Hashing a PDF's own bytes and then printing that hash inside the same PDF is self-referential — the act of printing the hash changes the file, which changes its hash. This plan resolves it with a **placeholder-substitution technique**, a real technique used in self-verifying documents:

1. Render the report to PDF with dompdf **compression disabled** (`Options::set('compress', false)`, confirm exact API against the installed version in Task 1) so the content stream contains literal, searchable ASCII text instead of a deflated binary blob.
2. The footer includes the literal string `Checksum SHA-256: ` followed by a **64-character placeholder** (`str_repeat('0', 64)` — same length as a real SHA-256 hex digest, so replacing it later doesn't shift any byte offsets in the file).
3. Compute `$checksum = hash('sha256', $draftPdfBytes)` over the **draft** bytes (placeholder still in place).
4. Produce the **delivered** file via `str_replace($placeholder, $checksum, $draftPdfBytes)` — same length in, same length out, so the PDF's internal structure (`/Length` entries, xref offsets) stays valid.
5. Store `$checksum` in `report_exports`. This is genuinely the delivered file's own fingerprint of everything *except* the checksum digits themselves.
6. **Verification** (`Cek Keaslian Laporan`): read the uploaded file's bytes, regex out the 64 hex chars following `Checksum SHA-256: `, replace that substring back with the placeholder to reconstruct the draft, `hash('sha256', ...)` it, and compare to the extracted value. If they match, the file is byte-for-byte what SIDONA generated (any edit anywhere — table data, footer, metadata — breaks the match). Then look up `report_exports` by the checksum to show who exported it and when.

This needs no new PDF-parsing dependency and is self-contained — verification doesn't even require the DB row to exist to prove authenticity, only to show provenance. Task 1 must empirically confirm the placeholder round-trips through a real generated PDF (write the Pest test first, as normal TDD, before trusting the technique).

**Reused:** Existing `#[Url]`-filtered Livewire index pattern (`app/Livewire/Audit/ActivityLogIndex.php`) for report filter bars. `role:auditor` middleware (reports live under the same Auditor-only umbrella as Phase 4, per the spec §3 role table which lists "laporan" under Auditor's capabilities). `Campaign::availableBalance()`/`verifiedDonationsTotal()`/`approvedDisbursementsTotal()` (`app/Models/Campaign.php`) for the balance summary report.

---

## File Structure

| File | Responsibility |
|---|---|
| `app/Services/ReportChecksum.php` | Placeholder constant, `stamp()` (draft bytes → [checksum, final bytes]), `verify()` (uploaded bytes → [valid, checksum]) |
| `database/migrations/*_create_report_exports_table.php` | `report_exports` schema |
| `app/Models/ReportExport.php` | Read/write model for the export log |
| `app/Livewire/Reports/DonationReport.php` + view | Filterable donation recap per program, bar chart, PDF export |
| `app/Livewire/Reports/DisbursementReport.php` + view | Filterable disbursement recap per program, PDF export |
| `app/Livewire/Reports/BalanceSummary.php` + view | All campaigns' balances, PDF export |
| `app/Livewire/Audit/ActivityLogIndex.php` (modify) | Adds an `exportPdf()` action to the existing viewer |
| `app/Livewire/Audit/LoginLogIndex.php` (modify) | Adds an `exportPdf()` action to the existing viewer |
| `app/Livewire/Reports/VerifyReport.php` + view | Upload-and-verify ("Cek Keaslian Laporan") |
| `resources/views/pdf/*.blade.php` | Print-oriented Blade templates rendered by dompdf (plain HTML/CSS, no Tailwind/Vite — dompdf can't process the build pipeline) |
| `routes/web.php` (modify) | Adds `/laporan/donasi`, `/laporan/penyaluran`, `/laporan/saldo`, `/laporan/cek-keaslian`, all `role:auditor` |
| `resources/views/components/layouts/app.blade.php` (modify) | Nav links to the report pages |

---

## Task 1: Install dompdf, `report_exports` table, `ReportChecksum` service

TDD test proves the placeholder-substitution technique actually survives a real dompdf render:

```php
// tests/Feature/ReportChecksumTest.php
use App\Services\ReportChecksum;

it('produces a checksum that verifies against the final stamped pdf bytes', function () {
    $draft = app(ReportChecksum::class)->renderDraft('<html><body>Contoh isi laporan.</body></html>');
    [$checksum, $final] = app(ReportChecksum::class)->stamp($draft);

    $result = app(ReportChecksum::class)->verify($final);

    expect($result['valid'])->toBeTrue();
    expect($result['checksum'])->toBe($checksum);
});

it('detects a modified pdf as invalid', function () {
    $draft = app(ReportChecksum::class)->renderDraft('<html><body>Contoh isi laporan.</body></html>');
    [, $final] = app(ReportChecksum::class)->stamp($draft);

    $tampered = str_replace('Contoh', 'Diubah', $final);

    expect(app(ReportChecksum::class)->verify($tampered)['valid'])->toBeFalse();
});
```

Steps:
1. `composer require barryvdh/laravel-dompdf` — confirm resolved version and check its `Options` API (`compress` key) against what actually installs; Laravel 13/PHP 8.4 compatibility to be verified here, same as Livewire/Pest in earlier phases.
2. `php artisan make:migration create_report_exports_table` — `id`, `string('reference', 20)->unique()`, `string('report_type')`, `json('filters')->nullable()`, `string('checksum', 64)`, `foreignId('user_id')->constrained()->cascadeOnDelete()`, `timestamp('created_at')->useCurrent()` (no `updated_at`, append-only like `ActivityLog`/`LoginLog`).
3. `app/Models/ReportExport.php` — `const UPDATED_AT = null;`, `$fillable`, `user(): BelongsTo`.
4. `app/Services/ReportChecksum.php`:
   - `public const PLACEHOLDER = <64 zero characters>;`
   - `renderDraft(string $html): string` — builds a `Dompdf` instance with `compress: false`, loads the HTML, renders, returns raw output bytes.
   - `stamp(string $draftBytes): array` — `$checksum = hash('sha256', $draftBytes); $final = str_replace(self::PLACEHOLDER, $checksum, $draftBytes); return [$checksum, $final];`
   - `verify(string $uploadedBytes): array` — regex `/Checksum SHA-256:\s*([0-9a-f]{64})/` against the bytes; if no match, `['valid' => false, 'checksum' => null]`; else reconstruct via `str_replace($matched, self::PLACEHOLDER, $uploadedBytes)`, rehash, compare, return `['valid' => bool, 'checksum' => $matched]`.

Commit: `feat: add report_exports table and self-verifying PDF checksum service`.

---

## Task 2: Donation report (Rekap Donasi per Program)

TDD test: filtering by campaign/status/date narrows the table; exporting writes a `report_exports` row and returns a PDF response whose bytes verify via `ReportChecksum::verify()`.

```php
// tests/Feature/DonationReportTest.php
it('lets an auditor filter the donation report', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    $campaign = Campaign::factory()->create();
    Donation::factory()->for($campaign)->create(['donor_name' => 'Budi', 'status' => DonationStatus::Verified]);
    Donation::factory()->for($campaign)->create(['donor_name' => 'Siti', 'status' => DonationStatus::Rejected]);

    Livewire::actingAs($auditor)
        ->test(DonationReport::class)
        ->set('status', 'verified')
        ->assertSee('Budi')
        ->assertDontSee('Siti');
});

it('exports a checksummed pdf and logs the export', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    Donation::factory()->create();

    $response = Livewire::actingAs($auditor)->test(DonationReport::class)->call('exportPdf');

    $export = ReportExport::where('report_type', 'donations')->latest()->first();
    expect($export)->not->toBeNull();
    expect($export->user_id)->toBe($auditor->id);

    $bytes = $response->effects['download']['content'] ?? null; // exact Livewire download-effect shape confirmed during implementation
    // fallback: assert via a direct HTTP-level test hitting a dedicated download route if Livewire's file-download testing API doesn't expose raw bytes cleanly
});
```

Note: Livewire 3's `#[Download]` / streamed-download testing ergonomics will be confirmed hands-on in this task — if asserting on the streamed bytes through `Livewire::test()` proves awkward, `exportPdf()` instead redirects to a plain signed `Route::get('/laporan/donasi/unduh/{export}', ...)` controller action that re-renders deterministically from the stored `filters` JSON, which is trivially testable with a normal `$this->get(...)->assertOk()` + byte-level checksum assertion. Pick whichever keeps the test meaningful without fighting the framework.

Implementation:
- `App\Livewire\Reports\DonationReport`: `#[Url]` filters (`campaign_id`, `status`, `from`, `to`), `render()` builds the filtered `Donation::with('campaign')` query for the table, plus a per-campaign sum of *verified* amounts for the bar chart (`Donation::query()->where('status', DonationStatus::Verified)->when(...)->get()->groupBy('campaign_id')->map->sum('amount')`).
- Bar chart: plain HTML/CSS in the PDF view — one `<div>` row per campaign, `<div style="width: {{ $percentOfMax }}%">` bar, no JS/SVG library (dompdf can't run either).
- `exportPdf(ReportChecksum $checksum)`: re-run the same filtered query, render `resources/views/pdf/donation-report.blade.php` (plain HTML, inline `<style>`, no `@vite`) to a draft, stamp it, create the `ReportExport` row (`report_type: 'donations'`, `filters: [...]`, `reference`, `checksum`, `user_id`), stream the final bytes as a PDF download.

Route: `Route::get('/laporan/donasi', DonationReport::class)->middleware('role:auditor')->name('reports.donations');`

Commit: `feat: add donation report with checksummed PDF export`.

---

## Task 3: Disbursement report (Rekap Penyaluran per Program)

Same shape as Task 2, no chart — just a filtered table (campaign, status, date range) plus PDF export logging to `report_exports` with `report_type: 'disbursements'`.

`App\Livewire\Reports\DisbursementReport` + `resources/views/pdf/disbursement-report.blade.php`.

Route: `Route::get('/laporan/penyaluran', DisbursementReport::class)->middleware('role:auditor')->name('reports.disbursements');`

Commit: `feat: add disbursement report with checksummed PDF export`.

---

## Task 4: Balance summary report (Ringkasan Saldo Tiap Program)

TDD test: the table shows each campaign's `verifiedDonationsTotal()`, `approvedDisbursementsTotal()`, `availableBalance()` (reusing `app/Models/Campaign.php`, built in Phase 3 — no new calculation logic).

`App\Livewire\Reports\BalanceSummary`: `render()` returns `Campaign::all()` (small table, no pagination needed — campaign count is bounded by seed data); view computes the three figures per row directly via the model methods. `exportPdf()` same stamp-and-log pattern as Task 2/3, `report_type: 'balance_summary'`.

Route: `Route::get('/laporan/saldo', BalanceSummary::class)->middleware('role:auditor')->name('reports.balance');`

Commit: `feat: add balance summary report with checksummed PDF export`.

---

## Task 5: PDF export on the activity log and login log viewers

Adds an `exportPdf()` method to the two Phase 4 viewers (`app/Livewire/Audit/ActivityLogIndex.php`, `app/Livewire/Audit/LoginLogIndex.php`) that renders the *currently filtered* rows to a checksummed PDF via the same `ReportChecksum` service, logging `report_type: 'activity_log'` / `'login_log'` with the active filters saved to `report_exports.filters`. Add an "Unduh PDF" button next to each viewer's filter bar. New Blade templates `resources/views/pdf/activity-log-report.blade.php` and `resources/views/pdf/login-log-report.blade.php`.

TDD test per viewer: exporting with a filter active produces a `report_exports` row whose stored `filters` match, and the PDF bytes verify.

Commit: `feat: add PDF export to activity and login log viewers`.

---

## Task 6: Cek Keaslian Laporan (verify) + nav links

TDD test: uploading a file produced by any of the above exports reports valid + shows the stored export's metadata (who/when/type); uploading an unrelated PDF (or a `.txt` file) reports invalid without erroring.

```php
// tests/Feature/VerifyReportTest.php
it('confirms a genuine exported report and shows who exported it', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);
    // generate a real export via DonationReport::exportPdf() or ReportChecksum directly to get real bytes + a matching report_exports row

    Livewire::actingAs($auditor)
        ->test(VerifyReport::class)
        ->set('file', $uploadedFakeFile)
        ->call('check')
        ->assertSet('result.valid', true);
});

it('reports an unrelated file as not a valid SIDONA report', function () {
    $auditor = User::factory()->create(['role' => UserRole::Auditor]);

    Livewire::actingAs($auditor)
        ->test(VerifyReport::class)
        ->set('file', \Illuminate\Http\UploadedFile::fake()->create('random.pdf', 10))
        ->call('check')
        ->assertSet('result.valid', false);
});
```

`App\Livewire\Reports\VerifyReport` (`use WithFileUploads;`): `public $file;`, `public ?array $result = null;`, `check(ReportChecksum $checksum)`: read the uploaded file's raw contents, `$this->result = $checksum->verify($contents)`; if valid, attach the matching `ReportExport` (by checksum) to `$this->result` for display (exported by, when, report type).

View: upload form + result panel (green "Laporan asli, diekspor oleh {name} pada {date}" / red "Berkas ini bukan laporan SIDONA yang sah, atau telah diubah sejak diterbitkan").

Route: `Route::get('/laporan/cek-keaslian', VerifyReport::class)->middleware('role:auditor')->name('reports.verify');`

Nav: add "Laporan Donasi", "Laporan Penyaluran", "Ringkasan Saldo", "Cek Keaslian Laporan" links inside the existing `@if (auth()->user()->isAuditor())` block in `resources/views/components/layouts/app.blade.php` (built in Phase 4).

Commit: `feat: add report authenticity verification page`.

---

## Manual verification checklist

1. `npm run build`, `php artisan serve`, log in as `auditor1@sidona.test`.
2. `/laporan/donasi` → filter by status/campaign → table narrows, bar chart reflects verified totals per campaign → click "Unduh PDF" → a PDF downloads showing a reference code and a 64-character checksum in the footer.
3. `/laporan/penyaluran` and `/laporan/saldo` → same export flow works.
4. `/audit/aktivitas` and `/audit/login` → "Unduh PDF" button exports the currently filtered view.
5. `/laporan/cek-keaslian` → upload one of the PDFs just downloaded → reports valid, shows exporter + timestamp.
6. Open that same downloaded PDF in a text editor, change one visible character, save, re-upload → reports invalid.
7. Upload an unrelated PDF or a `.txt` renamed to `.pdf` → reports invalid, no server error.
8. Log in as `admin@sidona.test` → none of the `/laporan/...` routes are reachable (403), no nav links shown.
9. Run `php artisan test` → all tests (Phase 1–5) pass.
10. `php artisan migrate:fresh --seed` once more afterward to leave the dev database clean.
