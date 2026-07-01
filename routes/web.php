<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\NotificationController as PlatformNotificationController;
use App\Http\Controllers\Platform\ProductController as PlatformProductController;
use App\Http\Controllers\Platform\SupportController as PlatformSupportController;
use App\Http\Controllers\Platform\VerificationController as PlatformVerificationController;
use App\Http\Controllers\Platform\WalletController as PlatformWalletController;
use App\Http\Controllers\Tenant\CustomerDashboardController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\NotificationController as TenantNotificationController;
use App\Http\Controllers\Tenant\ProductController as TenantProductController;
use App\Http\Controllers\Tenant\SupportController as TenantSupportController;
use App\Http\Controllers\Tenant\VerificationController as TenantVerificationController;
use App\Http\Controllers\Tenant\WalletController as TenantWalletController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'guest.welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email')->middleware('throttle:6,1');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'verified', 'role:Super Admin'])
    ->prefix('platform')
    ->as('platform.')
    ->group(function (): void {
        Route::get('dashboard', PlatformDashboardController::class)->name('dashboard');
        Route::get('wallet', PlatformWalletController::class)->name('wallet.index');
        Route::get('verifications', PlatformVerificationController::class)->name('verification.index');
        Route::prefix('notifications')->as('notifications.')->group(function (): void {
            Route::get('/', [PlatformNotificationController::class, 'index'])->name('index');
            Route::get('queue', [PlatformNotificationController::class, 'queue'])->name('queue');
            Route::get('failed', [PlatformNotificationController::class, 'failed'])->name('failed');
            Route::get('deliveries/{delivery}', [PlatformNotificationController::class, 'delivery'])->name('delivery');
            Route::post('deliveries/{delivery}/retry', [PlatformNotificationController::class, 'retry'])->name('delivery.retry');
            Route::get('{notification}', [PlatformNotificationController::class, 'show'])->name('show');
        });
        Route::get('support', [PlatformSupportController::class, 'index'])->name('support.index');
        Route::get('support/{reference}', [PlatformSupportController::class, 'show'])->name('support.show');
        Route::post('support/{reference}/assignments', [PlatformSupportController::class, 'assign'])->name('support.assignments.store');
        Route::patch('support/{reference}/status', [PlatformSupportController::class, 'status'])->name('support.status');
        Route::post('support/{reference}/internal-notes', [PlatformSupportController::class, 'storeInternalNote'])->name('support.internal-notes.store');
        Route::get('products', [PlatformProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [PlatformProductController::class, 'create'])->name('products.create');
        Route::post('products', [PlatformProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [PlatformProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [PlatformProductController::class, 'update'])->name('products.update');
        Route::patch('products/{product}/status', [PlatformProductController::class, 'status'])->name('products.status');
        Route::post('products/capabilities', [PlatformProductController::class, 'updateCapabilities'])->name('products.capabilities.store');
        Route::get('categories', [PlatformProductController::class, 'categories'])->name('categories.index');
        Route::post('categories', [PlatformProductController::class, 'storeCategory'])->name('categories.store');
        Route::get('provider-mappings', [PlatformProductController::class, 'providerMappings'])->name('provider-mappings.index');
        Route::post('provider-mappings', [PlatformProductController::class, 'storeProviderMapping'])->name('provider-mappings.store');
        Route::patch('provider-mappings/{mapping}/status', [PlatformProductController::class, 'providerMappingStatus'])->name('provider-mappings.status');
        Route::get('tenant-products', [PlatformProductController::class, 'tenantProducts'])->name('tenant-products.index');
        Route::post('tenant-products', [PlatformProductController::class, 'storeTenantProduct'])->name('tenant-products.store');
    });

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('t/{tenant:slug}')
    ->as('tenant.')
    ->group(function (): void {
        Route::get('dashboard', TenantDashboardController::class)->name('dashboard');
        Route::get('customer', CustomerDashboardController::class)->name('customer.dashboard');
        Route::middleware('permission:dashboard.view')->prefix('wallet')->as('wallet.')->group(function (): void {
            Route::get('/', [TenantWalletController::class, 'overview'])->name('index');
            Route::get('transactions', [TenantWalletController::class, 'transactions'])->name('transactions');
            Route::get('funding', [TenantWalletController::class, 'funding'])->name('funding.index');
            Route::post('funding', [TenantWalletController::class, 'initializeFunding'])->name('funding.store');
            Route::get('funding/{reference}', [TenantWalletController::class, 'fundingStatus'])->name('funding.show');
            Route::get('reservations', [TenantWalletController::class, 'reservations'])->name('reservations');
        });
        Route::middleware('permission:dashboard.view')->prefix('verification')->as('verification.')->group(function (): void {
            Route::get('/', [TenantVerificationController::class, 'index'])->name('index');
            Route::get('services', [TenantVerificationController::class, 'services'])->name('services');
            Route::post('request', [TenantVerificationController::class, 'submit'])->name('request.store');
            Route::get('history', [TenantVerificationController::class, 'history'])->name('history');
            Route::get('result/{reference}', [TenantVerificationController::class, 'result'])->name('result');
            Route::get('{reference}', [TenantVerificationController::class, 'show'])->name('show');
        });
        Route::get('products', TenantProductController::class)->name('products.index');
        Route::middleware('permission:notifications.view')->prefix('notifications')->as('notifications.')->group(function (): void {
            Route::get('/', [TenantNotificationController::class, 'index'])->name('index');
            Route::get('unread', [TenantNotificationController::class, 'unread'])->name('unread');
            Route::get('read', [TenantNotificationController::class, 'read'])->name('read');
            Route::get('archived', [TenantNotificationController::class, 'archived'])->name('archived');
            Route::get('preferences', [TenantNotificationController::class, 'preferences'])->middleware('permission:notifications.preferences')->name('preferences');
            Route::put('preferences', [TenantNotificationController::class, 'updatePreferences'])->middleware('permission:notifications.preferences')->name('preferences.update');
            Route::get('{notification}', [TenantNotificationController::class, 'show'])->name('show');
            Route::patch('{notification}/read', [TenantNotificationController::class, 'markRead'])->name('read.update');
            Route::patch('{notification}/archive', [TenantNotificationController::class, 'archive'])->name('archive');
        });
        Route::middleware('permission:support.tickets.view')->prefix('support')->as('support.')->group(function (): void {
            Route::get('/', [TenantSupportController::class, 'index'])->name('index');
            Route::post('/', [TenantSupportController::class, 'store'])->middleware('permission:support.tickets.create')->name('store');
            Route::get('{reference}', [TenantSupportController::class, 'show'])->name('show');
            Route::post('{reference}/notes', [TenantSupportController::class, 'storeNote'])->middleware('permission:support.tickets.comment')->name('notes.store');
        });
    });
