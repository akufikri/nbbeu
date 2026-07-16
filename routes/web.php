<?php

use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\DocumentController;
use App\Http\Controllers\Member\RenewalController;
use App\Http\Controllers\Membership\CardVerificationController;
use App\Http\Controllers\Membership\RegistrationController;
use App\Http\Controllers\Membership\RegistrationStatusController;
use App\Http\Controllers\Membership\ToyyibpayWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OrgStructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/org-structure', [OrgStructureController::class, 'index'])->name('org-structure');
Route::view('/terms', 'public.terms')->name('terms');
Route::view('/privacy', 'public.privacy')->name('privacy');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{post}', [BlogController::class, 'show'])->name('show');
});

Route::prefix('register')->name('registration.')->group(function () {
    Route::get('/', [RegistrationController::class, 'create'])->name('create');
    Route::post('/', [RegistrationController::class, 'store'])->name('store')->middleware('throttle:10,1');
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
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/documents/card', [DocumentController::class, 'card'])->name('documents.card');
        Route::get('/documents/certificate', [DocumentController::class, 'certificate'])->name('documents.certificate');
        Route::post('/renewal', [RenewalController::class, 'store'])->name('renewal');
    });
});

require __DIR__.'/auth.php';
