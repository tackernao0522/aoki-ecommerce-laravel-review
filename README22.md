## 63 その他(route 修正、レスポンシブ修正など)

### ルート情報の編集

新規登録はしない、要基礎画面不要<br>
->registration, welcome コメントアウト<br>

OwnersController::class, show は今回使わない<br>

```
Route::resource('owners', OwnersController::class)
  ->except(['show']);
```

### View 側の編集

<x-responsive-nav-link>にもリンク追加<br>

レスポンシブ対応<br>
x 方向(横方向)の margin, padding に md: をつける（768px 以上、タブレット）<br>

### ハンズオン

- `routes/admin.php`を編集<br>

```php:admin.php
<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Admin\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\OwnersController;
use Illuminate\Support\Facades\Route;

// コメントアウト
// Route::get('/', function () {
//   return view('admin.welcome');
// });

// 編集
Route::resource('owners', OwnersController::class)
  ->middleware('auth:admin')
  ->except(['show']);

Route::prefix('expired-owners')
  ->middleware('auth:admin')
  ->group(function () {
    Route::get('index', [OwnersController::class, 'expiredOwnerIndex'])->name(
      'expired-owners.index'
    );
    Route::post('destroy/{owner}', [
      OwnersController::class,
      'expiredOwnerDestroy',
    ])->name('expired-owners.destroy');
  });

Route::get('/dashboard', function () {
  return view('admin.dashboard');
})
  ->middleware(['auth:admin'])
  ->name('dashboard');

Route::middleware('guest')->group(function () {
  Route::get('register', [RegisteredUserController::class, 'create'])->name(
    'register'
  );

  Route::post('register', [RegisteredUserController::class, 'store']);

  Route::get('login', [AuthenticatedSessionController::class, 'create'])->name(
    'login'
  );

  Route::post('login', [AuthenticatedSessionController::class, 'store']);

  Route::get('forgot-password', [
    PasswordResetLinkController::class,
    'create',
  ])->name('password.request');

  Route::post('forgot-password', [
    PasswordResetLinkController::class,
    'store',
  ])->name('password.email');

  Route::get('reset-password/{token}', [
    NewPasswordController::class,
    'create',
  ])->name('password.reset');

  Route::post('reset-password', [NewPasswordController::class, 'store'])->name(
    'password.update'
  );
});

Route::middleware('auth:admin')->group(function () {
  Route::get('verify-email', [
    EmailVerificationPromptController::class,
    '__invoke',
  ])->name('verification.notice');

  Route::get('verify-email/{id}/{hash}', [
    VerifyEmailController::class,
    '__invoke',
  ])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

  Route::post('email/verification-notification', [
    EmailVerificationNotificationController::class,
    'store',
  ])
    ->middleware('throttle:6,1')
    ->name('verification.send');

  Route::get('confirm-password', [
    ConfirmablePasswordController::class,
    'show',
  ])->name('password.confirm');

  Route::post('confirm-password', [
    ConfirmablePasswordController::class,
    'store',
  ]);

  Route::post('logout', [
    AuthenticatedSessionController::class,
    'destroy',
  ])->name('logout');
});
```

- `resources/views/layouts/admin-navigation.blade.php`を編集<br>

```php:admin-navigation.blade.php
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <div class="w-12">
                        <a href="{{ route('admin.dashboard') }}">
                            <x-application-logo class="block h-10 w-auto fill-current text-gray-600" />
                        </a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.owners.index')" :active="request()->routeIs('admin.owners.index')">
                        オーナー管理
                    </x-nav-link>
                    <x-nav-link :href="route('admin.expired-owners.index')" :active="request()->routeIs('admin.expired-owners.index')">
                        期限切れオーナー一覧
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('admin.logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            // 追加
            <x-responsive-nav-link :href="route('admin.owners.index')" :active="request()->routeIs('admin.owners.index')">
                オーナー管理
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.expired-owners.index')" :active="request()->routeIs('admin.expired-owners.index')">
                期限切れオーナー一覧
            </x-responsive-nav-link>
            // ここまで
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Authentication -->
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('admin.logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
```

- `resources/views/admin/owners/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            オーナー一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                // 編集 "md:p-6"とする
                <div class="md:p-6 bg-white border-b border-gray-200">
                    <section class="text-gray-600 body-font">
                        // 編集 "md:px-5"とする
                        <div class="container md:px-5 mx-auto">
                            <div class="lg:w-2/3 w-full mx-auto overflow-auto">
                                <x-flash-message status="session('status')" />
                                <div class="flex justify-end mb-4">
                                    <button onclick="location.href='{{ route('admin.owners.create') }}'"
                                        class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">新規登録する</button>
                                </div>
                                <table class="table-auto w-full text-left whitespace-no-wrap">
                                    <thead>
                                        <tr>
                                            // 編集 "md:px-4"とする
                                            <th
                                                class="md:px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                                                名前</th>
                                            // 編集 "md:px-4"とする
                                            <th
                                                class="md:px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                                メールアドレス</th>
                                            // 編集 "md:px-4"とする
                                            <th
                                                class="md:px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                                作成日</th>
                                            // 編集 "md:px-4"とする
                                            <th
                                                class="md:px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tr rounded-br">
                                            </th>
                                            // 編集 "md:px-4"とする
                                            <th
                                                class="md:px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tr rounded-br">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($owners as $owner)
                                            <tr>
                                                // 編集 "md:px-4"とする
                                                <td class="md:px-4 py-3">{{ $owner->name }}</td>
                                                <td class="md:px-4 py-3">{{ $owner->email }}</td>
                                                <td class="md:px-4 py-3">{{ $owner->created_at->diffForHumans() }}
                                                // ここまで
                                                </td>
                                                // 編集 "md:px-4"とする
                                                <td class="md:px-4 py-3">
                                                    <button type="button"
                                                        onclick="location.href='{{ route('admin.owners.edit', $owner->id) }}'"
                                                        class="text-white bg-indigo-400 border-0 py-2 px-4 focus:outline-none hover:bg-indigo-500 rounded">編集</button>
                                                </td>
                                                <form id="delete_{{ $owner->id }}"
                                                    action="{{ route('admin.owners.destroy', $owner->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    // 編集 "md:px-4"とする
                                                    <td class="md:px-4 py-3">
                                                        <a href="#" data-id="{{ $owner->id }}"
                                                            onclick="deletePost(this)" type="button"
                                                            class="text-white bg-red-400 border-0 py-2 px-4 focus:outline-none hover:bg-red-500 rounded">削除</a>
                                                    </td>
                                                </form>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $owners->links() }}
                            </div>
                        </div>
                    </section>

                    {{-- エロクアント
                    @foreach ($e_all as $e_owner)
                        {{ $e_owner->name }}
                        {{ $e_owner->created_at->diffForHumans() }}
                    @endforeach
                    <br>
                    クエリビルダ
                    @foreach ($q_get as $q_owner)
                        {{ $q_owner->name }}
                        {{ Carbon\Carbon::parse($q_owner->created_at)->diffForHumans() }}
                    @endforeach --}}
                </div>
            </div>
        </div>
    </div>
    <script>
        function deletePost(e) {
            'use strict';
            if (confirm('本当に削除してもいいですか？')) {
                document.getElementById('delete_' + e.dataset.id).submit();
            }
        }
    </script>
</x-app-layout>
```
