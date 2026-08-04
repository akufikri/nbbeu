<?php

use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\DocumentController;
use App\Http\Controllers\Member\PaymentHistoryController;
use App\Http\Controllers\Member\RenewalController;
use App\Http\Controllers\Member\UnionDuesController;
use App\Http\Controllers\Membership\CardVerificationController;
use App\Http\Controllers\Membership\RegistrationStatusController;
use App\Http\Controllers\Membership\ToyyibpayWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OrgStructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/org-structure', [OrgStructureController::class, 'index'])->name('org-structure');
Route::view('/terms', 'public.terms')->name('terms');
Route::view('/privacy', 'public.privacy')->name('privacy');
Route::get('/collective-agreement', fn () => view('public.collective-agreement', [
    'text' => \App\Models\Setting::get('collective_agreement_text'),
]))->name('collective-agreement');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{post}', [BlogController::class, 'show'])->name('show');
});

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

Route::prefix('register')->name('registration.')->group(function () {
    Route::get('/', fn () => view('membership.register-wizard'))->name('create');
    Route::get('/status', [RegistrationStatusController::class, 'show'])->name('status');
    Route::get('/return/{payment}', [ToyyibpayWebhookController::class, 'return'])->name('return');
});

Route::post('/webhooks/toyyibpay', [ToyyibpayWebhookController::class, 'callback'])
    ->name('registration.callback')
    ->middleware('throttle:60,1');

Route::get('/verify/{qrToken}', [CardVerificationController::class, 'show'])
    ->name('verify.card')
    ->middleware('throttle:60,1');

Route::get('/dashboard', [MemberDashboardController::class, 'index'])
    ->middleware(['auth:web', 'verified'])
    ->name('dashboard');

Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/payments', [PaymentHistoryController::class, 'index'])->name('member.payments');

    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/card', [DocumentController::class, 'cardPage'])->name('card');
        Route::get('/certificate', [DocumentController::class, 'certificatePage'])->name('certificate');
        Route::get('/documents/card', [DocumentController::class, 'card'])->name('documents.card');
        Route::get('/documents/certificate', [DocumentController::class, 'certificate'])->name('documents.certificate');
        Route::get('/renewal', [RenewalController::class, 'index'])->name('renewal.index');
        Route::post('/renewal', [RenewalController::class, 'store'])->name('renewal');
        Route::get('/union-dues', [UnionDuesController::class, 'index'])->name('union-dues');
        Route::post('/union-dues', [UnionDuesController::class, 'store'])->name('union-dues.store');
        Route::get('/union-dues/{mandate}/download', [UnionDuesController::class, 'download'])->name('union-dues.download');
    });
});

require __DIR__.'/auth.php';
