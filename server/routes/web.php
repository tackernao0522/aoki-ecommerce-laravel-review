<?php

use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('user.welcome');
});

Route::middleware('auth:users')
    ->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name(
            'items.index'
        );
        Route::get('show/{item}', [ItemController::class, 'show'])->name('items.show');
    });

Route::prefix('cart')
    ->middleware('auth:users')
    ->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::post('add', [CartController::class, 'add'])->name('cart.add');
        Route::post('delete/{item}', [CartController::class, 'delete'])->name('cart.delete');
        Route::get('checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::get('success', [CartController::class, 'success'])->name('cart.success');
    });

// Route::get('/dashboard', function () {
//     return view('user.dashboard');
// })->middleware(['auth:users'])->name('dashboard'); // 認証しているかどうか

Route::get('/component-test1', [ComponentTestController::class, 'showComponent1']);
Route::get('/component-test2', [ComponentTestController::class, 'showComponent2']);
Route::get('/servicecontainertest', [LifeCycleTestController::class, 'showServiceContainerTest']);
Route::get('/serviceprovidertest', [LifeCycleTestController::class, 'showServiceProviderTest']);

require __DIR__ . '/auth.php';
