<?php

use App\Http\Controllers\DownloadReportExportController;
use App\Livewire\Audit\ActivityLogIndex;
use App\Livewire\Audit\AnomalyDashboard;
use App\Livewire\Audit\LoginLogIndex;
use App\Livewire\Audit\VerifyIntegrity;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Campaigns\CampaignForm;
use App\Livewire\Campaigns\CampaignIndex;
use App\Livewire\Dashboard;
use App\Livewire\Disbursements\DisbursementForm;
use App\Livewire\Disbursements\DisbursementIndex;
use App\Livewire\Donations\DonationIndex;
use App\Livewire\Profile\ProfilePage;
use App\Livewire\Public\CampaignDetail;
use App\Livewire\Public\CampaignList;
use App\Livewire\Public\CampaignSubmit;
use App\Livewire\Public\DonationPay;
use App\Livewire\Public\DonationReceipt;
use App\Livewire\Public\DonationStatusCheck;
use App\Livewire\Reports\BalanceSummary;
use App\Livewire\Reports\DisbursementReport;
use App\Livewire\Reports\DonationReport;
use App\Livewire\Reports\VerifyReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('program.index');
});

Route::get('/program', CampaignList::class)->name('program.index');
Route::get('/program/{campaign}', CampaignDetail::class)->name('program.show');
Route::get('/ajukan-program', CampaignSubmit::class)->name('program.submit');
Route::get('/donasi/cek', DonationStatusCheck::class)->name('donations.check');
Route::get('/donasi/{reference}/bayar', DonationPay::class)->name('donations.pay');
Route::get('/donasi/{reference}', DonationReceipt::class)->name('donations.receipt');

Route::middleware('guest')->group(function () {
    Route::get('/login', LoginForm::class)->name('login');
});

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('program.index')->with('status', 'Anda sudah keluar.');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/profil', ProfilePage::class)->name('profile');

    Route::get('/campaigns', CampaignIndex::class)->name('campaigns.index');
    Route::get('/campaigns/create', CampaignForm::class)
        ->middleware('role:bendahara,admin')
        ->name('campaigns.create');
    Route::get('/campaigns/{campaign}/edit', CampaignForm::class)
        ->middleware('role:bendahara,admin')
        ->name('campaigns.edit');

    Route::get('/riwayat-donasi', DonationIndex::class)->name('donations.index');

    Route::get('/campaigns/{campaign}/penyaluran/ajukan', DisbursementForm::class)
        ->middleware('role:bendahara')
        ->name('disbursements.create');
    Route::get('/penyaluran', DisbursementIndex::class)->name('disbursements.index');

    Route::get('/audit/integritas', VerifyIntegrity::class)
        ->middleware('role:auditor')
        ->name('audit.integrity');
    Route::get('/audit/aktivitas', ActivityLogIndex::class)
        ->middleware('role:auditor')
        ->name('audit.activity');
    Route::get('/audit/login', LoginLogIndex::class)
        ->middleware('role:auditor')
        ->name('audit.login');
    Route::get('/audit/anomali', AnomalyDashboard::class)
        ->middleware('role:auditor')
        ->name('audit.anomalies');

    Route::get('/laporan/donasi', DonationReport::class)
        ->middleware('role:auditor')
        ->name('reports.donations');
    Route::get('/laporan/penyaluran', DisbursementReport::class)
        ->middleware('role:auditor')
        ->name('reports.disbursements');
    Route::get('/laporan/saldo', BalanceSummary::class)
        ->middleware('role:auditor')
        ->name('reports.balance');
    Route::get('/laporan/unduh/{reportExport:reference}', DownloadReportExportController::class)
        ->middleware('role:auditor')
        ->name('reports.download');
    Route::get('/laporan/cek-keaslian', VerifyReport::class)
        ->middleware('role:auditor')
        ->name('reports.verify');
});
