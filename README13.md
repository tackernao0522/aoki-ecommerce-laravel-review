# セクション 05: 管理者側

## 45 設計資料の紹介

### 要件定義〜基本設計

企画->要件定義->設計->実装->テスト->リリース<br>

上流工程 5 割 実装 2〜3 割<br>

https://qiita.com/Saku731/items/741fcf0f40dd989ee4f8 <br>

### 基本設計

画面設計(UI 設計)<br>
AdobeXD、Figma<br>
->今回は tailblock のテンプレートで作成<br>
https://tailblocks.cc/ <br>

機能設計 ・・ テーブル設計、ER 図<br>

### 基本設計リンク

URL 設計、テーブル設計、機能設計<br>
(Google スプレッドシート)<br>
https://docs.google.com/spreadsheets/d/1YIDqTKH2v2-n97kb2GNhWrcMGnJD84JMqTuzD_poMqo/edit#gid=0 <br>

ER 図(draw.id)<br>
https://drive.google.com/file/d/18sEk5LC-jJum-NU9JKNZibGRVX81aWE1/view <br>

## 46 アプリ名、ロゴ設定

アプリ名 ・・ .env ファイル<br>
APP_NAME= Umarche<br>

`Config/app.php`内で設定される<br>

ロゴ（ロゴ 作成 無料 などで検索）<br>
https://drive.google.com/file/d/1C2ooEDTFPp5cWr2B6gsYQnwKEhM3NmD3/view <br>

### ハンズオン

`.env`ファイルを編集<br>

```:.env
APP_NAME=Umarche # 編集
APP_ENV=local
APP_KEY=base64:kIiIWzZ988XaZKYB6TaM6fxktugDUr5fKaPJN0k2drk=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=ecommercedb-host
DB_PORT=3306
DB_DATABASE=ecommerce-database
DB_USERNAME=udemy_ecommerce
DB_PASSWORD=5t5a7k3a

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"

#Sendgrid用
MAIL_DRIVER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=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
MAIL_ENCRYPTION=tls
MAIL_FROM_NAME=Demo_funclub
MAIL_FROM_ADDRESS=takaki_5573031@yahoo.co.jp

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### ロゴ表示

`public`フォルダに直接置く ・・ 初期ファイル<br>
`storage`フォルダ ・・ フォルダ内画像は gitHub にアップしない<br>

表側(public)から見れるようにリンク<br>
`php artisan storage:link`<br>
`public/storage`リンクが生成される<br>

`asset()`ヘルパ関数で public 内のファイルを指定<br>

`asset("images/logo.png")を`components/application-logo.blade.php`に記載<br>

### ハンズオン

#### storage を使う場合

- `logo.png`ファイル`を`storage/app/public`ディレクトリに配置<br>

* `$ php artisan storage:link`を実行<br>

#### 今回は `public/images`ディレクトリを作成して`public/images/logo.png`で配置する<br>

- `resources/views/components/application-logo.blade.php`を編集<br>

```html:application-logo.blade.php
<img src="{{ asset("images/logo.png") }}">
```

- `resources/views/admin/auth/login.blade.php`を編集<br>

```html:login.blade.php
<x-guest-layout>
  <x-auth-card>
    管理者用
    <x-slot name="logo">
      <!-- 編集 -->
      <div class="w-28">
        <a href="/">
          <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
        </a>
      </div>
    </x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Validation Errors -->
    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <form method="POST" action="{{ route('admin.login') }}">
      @csrf

      <!-- Email Address -->
      <div>
        <x-label for="email" :value="__('Email')" />

        <x-input
          id="email"
          class="block mt-1 w-full"
          type="email"
          name="email"
          :value="old('email')"
          required
          autofocus
        />
      </div>

      <!-- Password -->
      <div class="mt-4">
        <x-label for="password" :value="__('Password')" />

        <x-input
          id="password"
          class="block mt-1 w-full"
          type="password"
          name="password"
          required
          autocomplete="current-password"
        />
      </div>

      <!-- Remember Me -->
      <div class="block mt-4">
        <label for="remember_me" class="inline-flex items-center">
          <input
            id="remember_me"
            type="checkbox"
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            name="remember"
          />
          <span class="ml-2 text-sm text-gray-600">
            {{ __('Remember me') }}
          </span>
        </label>
      </div>

      <div class="flex items-center justify-end mt-4">
        @if (Route::has('admin.password.request'))
        <a
          class="underline text-sm text-gray-600 hover:text-gray-900"
          href="{{ route('admin.password.request') }}"
        >
          {{ __('Forgot your password?') }}
        </a>
        @endif

        <x-button class="ml-3">
          {{ __('Log in') }}
        </x-button>
      </div>
    </form>
  </x-auth-card>
</x-guest-layout>
```

## 47 リソースコントローラ

https://readouble.com/laravel/8.x/ja/controllers.html (リソースコントローラ) <br>

### リソース(Restful)コントローラ

CRUD(新規作成、表示、更新、削除)<br>

C(create, store) R(index, show, edit) U(update) D(destroy)<br>

表示 ・・ GET、DB に保存 ・・ POST<br>

|       動詞        |         URI          | アクション |    ルート名    |
| :---------------: | :------------------: | :--------: | :------------: |
|        GET        |       /photos        |   index    |  photos.index  |
|        GET        |    /photos/create    |   create   | photos.create  |
|       POST        |       /photos        |   store    |  photos.store  |
|        GET        |   /photos/{photo}    |    show    |  photos.show   |
|        GET        | /photos/{photo}/edit |    edit    |  photos.edit   |
| (POST)<-PUT/PATCH |   /photos/{photo}    |   update   | photos.update  |
|  (POST)<-DELETE   |   /photos/{photo}    |  destory   | photos.destroy |

### URL 設計を見ながら

https://docs.google.com/spreadsheets/d/1YIDqTKH2v2-n97kb2GNhWrcMGnJD84JMqTuzD_poMqo/edit#gid=0 <br>

POST の場合は画面不要(blade 不要)<br>
オーナー登録画面 ・・ GET オーナー登録 ・・ POST<br>

URL /admin/owners<br>
action index<br>
名前付きルート route('admin.owners.index')<br>
View ファイル(blade) view('admin.owners.index')<br>
コントローラ Admin\OwnersController@index<br>

生成コマンド<br>
php artisan make:controller Admin/OwnersController --resource<br>

ルート側<br>

```php:admin.php
Route::resource('owners', OwnersController:class)->middleware('auth:admin'));
```

### ハンズオン

- `$ php artisan make:controller Admin/OwnersController --resource`を実行<br>

* `routes/admin.php`を編集<br>

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
use App\Http\Controllers\Admin\OwnersConroller;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('admin.welcome');
});

// 追記
Route::resource('owners', OwnersConroller::class)->middleware('auth:admin');

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

- `app/Http/Controllers/Admin/OwnersController.php`を編集<br>

```php:OwnersController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OwnersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:admin');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    dd('オーナー一覧です');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }
}
```

- `resources/views/layouts/admin-navigation.blade.php`を編集<br>

```html:admin-navigation.blade.php
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
  <!-- Primary Navigation Menu -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex">
        <!-- Logo -->
        <div class="shrink-0 flex items-center">
          <!-- 編集 -->
          <div class="w-12">
            <a href="{{ route('admin.dashboard') }}">
              <x-application-logo
                class="block h-10 w-auto fill-current text-gray-600"
              />
            </a>
          </div>
          <!-- ここまで -->
        </div>

        <!-- Navigation Links -->
        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
          <x-nav-link
            :href="route('admin.dashboard')"
            :active="request()->routeIs('admin.dashboard')"
          >
            {{ __('Dashboard') }}
          </x-nav-link>
          <!-- 追記 -->
          <x-nav-link
            :href="route('admin.owners.index')"
            :active="request()->routeIs('admin.owners.index')"
          >
            オーナー管理
          </x-nav-link>
          <!-- ここまで -->
        </div>
      </div>

      <!-- Settings Dropdown -->
      <div class="hidden sm:flex sm:items-center sm:ml-6">
        <x-dropdown align="right" width="48">
          <x-slot name="trigger">
            <button
              class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
            >
              <div>{{ Auth::user()->name }}</div>

              <div class="ml-1">
                <svg
                  class="fill-current h-4 w-4"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
            </button>
          </x-slot>

          <x-slot name="content">
            <!-- Authentication -->
            <form method="POST" action="{{ route('admin.logout') }}">
              @csrf

              <x-dropdown-link
                :href="route('admin.logout')"
                onclick="event.preventDefault();
                                                this.closest('form').submit();"
              >
                {{ __('Log Out') }}
              </x-dropdown-link>
            </form>
          </x-slot>
        </x-dropdown>
      </div>

      <!-- Hamburger -->
      <div class="-mr-2 flex items-center sm:hidden">
        <button
          @click="open = ! open"
          class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
        >
          <svg
            class="h-6 w-6"
            stroke="currentColor"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              :class="{'hidden': open, 'inline-flex': ! open }"
              class="inline-flex"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
            <path
              :class="{'hidden': ! open, 'inline-flex': open }"
              class="hidden"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Responsive Navigation Menu -->
  <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    <div class="pt-2 pb-3 space-y-1">
      <x-responsive-nav-link
        :href="route('admin.dashboard')"
        :active="request()->routeIs('admin.dashboard')"
      >
        {{ __('Dashboard') }}
      </x-responsive-nav-link>
    </div>

    <!-- Responsive Settings Options -->
    <div class="pt-4 pb-1 border-t border-gray-200">
      <div class="px-4">
        <div class="font-medium text-base text-gray-800">
          {{ Auth::user()->name }}
        </div>
        <div class="font-medium text-sm text-gray-500">
          {{ Auth::user()->email }}
        </div>
      </div>

      <div class="mt-3 space-y-1">
        <!-- Authentication -->
        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf

          <x-responsive-nav-link
            :href="route('admin.logout')"
            onclick="event.preventDefault();
                                        this.closest('form').submit();"
          >
            {{ __('Log Out') }}
          </x-responsive-nav-link>
        </form>
      </div>
    </div>
  </div>
</nav>
```
