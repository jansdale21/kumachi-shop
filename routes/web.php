<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KioskCartController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\KioskMenuController;
use App\Http\Controllers\KioskOrderController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): View|RedirectResponse {
    $user = Auth::user();

    if ($user instanceof User && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user instanceof User && $user->isStaff()) {
        return redirect()->route('kiosk');
    }

    return redirect()->route('home');
})->name('home.redirect');

Route::middleware('redirect.staff.kiosk')->group(function () {
    Route::get('/home', [CustomerController::class, 'home'])->name('home');
    Route::get('/menu', [CustomerController::class, 'menu'])->name('menu');
    Route::get('/products/{product}', [CustomerController::class, 'product'])->name('products.show');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user instanceof User && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user instanceof User && $user->isStaff()) {
        return redirect()->route('kiosk');
    }

    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin dashboard route is registered inside the auth group below.

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/kiosk', [KioskController::class, 'index'])
        ->middleware(['verified', 'staff'])
        ->name('kiosk');

    Route::prefix('kiosk')
        ->name('kiosk.')
        ->middleware(['verified', 'staff'])
        ->group(function () {
            Route::get('/menu', [KioskMenuController::class, 'menu'])->name('menu');
            Route::get('/products/{product}', [KioskMenuController::class, 'product'])->name('products.show');

            Route::get('/cart', [KioskCartController::class, 'index'])->name('cart.index');
            Route::post('/cart/clear', [KioskCartController::class, 'clear'])->name('cart.clear');
            Route::post('/products/{product}/cart', [KioskCartController::class, 'store'])->name('cart.store');
            Route::patch('/cart/items/{cartItem}', [KioskCartController::class, 'update'])->name('cart.items.update');
            Route::delete('/cart/items/{cartItem}', [KioskCartController::class, 'destroy'])->name('cart.items.destroy');

            Route::get('/checkout', [KioskOrderController::class, 'checkout'])->name('checkout.index');
            Route::post('/checkout', [KioskOrderController::class, 'placeOrder'])->name('checkout.store');
            Route::get('/orders/{order}/receipt', [KioskOrderController::class, 'receipt'])->name('orders.receipt');
        });

    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->middleware(['verified', 'admin'])
        ->name('admin.dashboard');

    Route::middleware(['verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', CategoryController::class)
            ->except('show');

        Route::resource('products', ProductController::class)
            ->except('show');

        Route::resource('orders', OrderController::class)
            ->only(['index', 'show']);

        Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');

        Route::resource('promotions', PromotionController::class)
            ->except('show');

        Route::resource('users', UserController::class)
            ->except('show');

        Route::resource('addons', AddonController::class)
            ->except('show');

        Route::resource('suppliers', SupplierController::class);

        Route::resource('inventories', InventoryController::class);

        Route::resource('purchase-orders', PurchaseOrderController::class)
            ->except(['edit']);

        Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [AdminReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('redirect.staff.kiosk')->group(function () {
        Route::get('/rewards', [CustomerController::class, 'rewards'])->name('rewards');
        Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders');
        Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/receipt', [CustomerOrderController::class, 'receipt'])->name('orders.receipt');
        Route::post('/orders/{order}/reorder', [CustomerOrderController::class, 'reorder'])->name('orders.reorder');
        Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/products/{product}/cart', [CartController::class, 'store'])->name('cart.store');
        Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');
        Route::get('/checkout', [CustomerOrderController::class, 'checkout'])->name('checkout.index');
        Route::post('/checkout', [CustomerOrderController::class, 'placeOrder'])->name('checkout.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{address}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');
    Route::patch('/profile/addresses/{address}/default', [ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.default');
    Route::post('/profile/payment-methods', [ProfileController::class, 'storePaymentMethod'])->name('profile.payment-methods.store');
    Route::put('/profile/payment-methods/{paymentMethod}', [ProfileController::class, 'updatePaymentMethod'])->name('profile.payment-methods.update');
    Route::delete('/profile/payment-methods/{paymentMethod}', [ProfileController::class, 'destroyPaymentMethod'])->name('profile.payment-methods.destroy');
    Route::patch('/profile/payment-methods/{paymentMethod}/default', [ProfileController::class, 'setDefaultPaymentMethod'])->name('profile.payment-methods.default');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/notifications/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.read');
});

require __DIR__.'/auth.php';
