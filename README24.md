## 68 Shop 作成 その 2

https://readouble.com/laravel/8.x/ja/database.html (データベーストランザクション)<br>

- `app/Http/Controllers/Admin/OwnersController.php`を編集<br>

```php:OwnersController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner; // eloquent エロクアント
use App\Models\Shop; // 追記
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // QueryBuilder クエリビルダ
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class OwnersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:admin');
  }

  public function index()
  {
    // $date_now = Carbon::now();
    // $date_parse = Carbon::parse(now());
    // echo $date_now . '<br>';
    // echo $date_now->year . '<br>';
    // echo $date_parse . '<br>';

    // $e_all = Owner::all();
    // $q_get = DB::table('owners')->select('name', 'created_at')->get();
    // $q_first = DB::table('owners')->select('name')->first();

    // $c_test = collect([
    //     'name' => 'テスト',
    // ]);

    // var_dump($q_first);

    // dd($e_all, $q_get, $q_first, $c_test);

    $owners = Owner::select('id', 'name', 'email', 'created_at')->paginate(3);

    return view('admin.owners.index', compact('owners'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('admin.owners.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  // 編集
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:owners',
      'password' => 'required|string|confirmed|min:8',
    ]);

    try {
      DB::transaction(function () use ($request) {
        $owner = Owner::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => Hash::make($request->password),
        ]);

        Shop::create([
          'owner_id' => $owner->id,
          'name' => '店名を入力してください',
          'information' => '',
          'filename' => '',
          'is_selling' => true,
        ]);
      }, 2);
    } catch (Throwable $e) {
      Log::error($e);
      throw $e;
    }

    return redirect()
      ->route('admin.owners.index')
      ->with([
        'message' => 'オーナー登録を実施しました。',
        'status' => 'info',
      ]);
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
    $owner = Owner::findOrFail($id);

    return view('admin.owners.edit', compact('owner'));
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
    $owner = Owner::findOrFail($id);

    // $request->validate([
    //     'name' => 'required|string|max:255',
    //     'email' => 'required|string|email|max:255|unique:owners',
    //     'password' => 'required|string|confirmed|min:8',
    // ]);

    $owner->name = $request->name;
    $owner->email = $request->email;
    $owner->password = Hash::make($request->password);

    $owner->save();

    return redirect()
      ->route('admin.owners.index')
      ->with([
        'message' => 'オーナ情報を更新しました。',
        'status' => 'info',
      ]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    Owner::findOrFail($id)->delete(); // ソフトデリート

    return redirect()
      ->back()
      ->with([
        'message' => 'オーナー情報を削除しました。',
        'status' => 'alert',
      ]);
  }

  public function expiredOwnerIndex()
  {
    $expiredOwners = Owner::onlyTrashed()->get(); // 論理削除したデータを取得できる

    return view('admin.expired-owners', compact('expiredOwners'));
  }

  public function expiredOwnerDestroy($id)
  {
    Owner::onlyTrashed()
      ->findOrFail($id)
      ->forceDelete(); // 完全に削除する(物理的削除)

    return redirect()
      ->back()
      ->with([
        'message' => '期限切れオーナー情報を削除しました。',
        'status' => 'alert',
      ]);
  }
}
```

- 新規オーナー登録してみる<br>

* `resources/views/admin/owners/edit.blade.php`を編集<br>

```php:edit.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            オーナー情報編集
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <section class="text-gray-600 body-font relative">
                        <div class="container px-5 mx-auto">
                            <div class="flex flex-col text-center w-full mb-12">
                                <h1 class="sm:text-3xl text-2xl font-medium title-font mb-4 text-gray-900">オーナー情報編集</h1>
                            </div>
                            <div class="lg:w-1/2 md:w-2/3 mx-auto">
                                <!-- Validation Errors -->
                                <x-auth-validation-errors class="mb-4" :errors="$errors" />
                                <form method="post" action="{{ route('admin.owners.update', $owner->id) }}">
                                    @method('PUT')
                                    @csrf
                                    <div class="-m-2">
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="name" class="leading-7 text-sm text-gray-600">オーナー名</label>
                                                <input type="text" id="name" name="name"
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                                    value="{{ old('name', $owner->name) }}" required />
                                            </div>
                                        </div>
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="email"
                                                    class="leading-7 text-sm text-gray-600">メールアドレス</label>
                                                <input type="email" id="email" name="email"
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                                    value="{{ old('email', $owner->email) }}" required />
                                            </div>
                                        </div>
                                        // 追加
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="shop" class="leading-7 text-sm text-gray-600">店名</label>
                                                <div
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ $owner->shop->name }}</div>
                                            </div>
                                        </div>
                                        // ここまで
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="password"
                                                    class="leading-7 text-sm text-gray-600">パスワード</label>
                                                <input type="password" id="password" name="password"
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                                    required />
                                            </div>
                                        </div>
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="password_confirmation"
                                                    class="leading-7 text-sm text-gray-600">パスワード確認</label>
                                                <input type="password" id="password_confirmation"
                                                    name="password_confirmation"
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                                    required />
                                            </div>
                                        </div>
                                        <div class="p-2 w-full flex justify-around mt-4">
                                            <button type="button"
                                                onclick="location.href='{{ route('admin.owners.index') }}'"
                                                class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-gray-400 rounded text-lg">戻る</button>
                                            <button type="submit"
                                                class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">更新する</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

## 69 Shop Delete(カスケード)

### Shop の削除 カスケード

Owner->Shop と<br>
外部キー制約を設定しているため追加設定が必要<br>

```
$table->foreginId('owner_id')
  ->constrained()
  ->onUpdate('cascade')
  ->onDelete('cascade');
```

### ハンズオン

- `database/migrations/create_shops_table.php`を編集<br>

```php:create_shops_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('shops', function (Blueprint $table) {
      $table->id();
      // 編集
      $table
        ->foreignId('owner_id')
        ->constrained()
        ->onUpdate('cascade')
        ->onDelete('cascade');
      // ここまで
      $table->string('name');
      $table->text('information');
      $table->string('filename');
      $table->boolean('is_selling');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('shops');
  }
}
```

- `$ php artisan migrate:refresh --seed`を実行<br>

## 70 Shop Index (ルート, コントローラ, ビュー)

### Shop 表示までの設定

Route<br>
Index, edit, update の３つ<br>
`owner.shops.index`など<br>

View<br>
ロゴサイズ調整, owner-navigation<br>

Controller・・ShopController<br>
`__construnct`で`$this->middleware('auth:owners');`<br>

index メソッド

```php:ShopController.php
use Illuminate\Support\Facades\Auth;

$ownerId = Auth::id(); // 認証されているid(ログインしているid)
$shops = Shop::where('owner_id', $ownerId)->get();
// whereは検索条件
```

### ハンズオン

- `$ php artisan make:controller Owner/ShopController`を実行<br>

* `routes/owner.php`を編集<br>

```php:owner.php
<?php

use App\Http\Controllers\Owner\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Owner\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Owner\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Owner\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Owner\Auth\NewPasswordController;
use App\Http\Controllers\Owner\Auth\PasswordResetLinkController;
use App\Http\Controllers\Owner\Auth\RegisteredUserController;
use App\Http\Controllers\Owner\Auth\VerifyEmailController;
use App\Http\Controllers\Owner\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('owner.welcome');
});

// 追加
Route::prefix('shops')
  ->middleware('auth:owners')
  ->group(function () {
    Route::get('index', [ShopController::class, 'index'])->name('shops.index');
    Route::get('edit/{shop}', [ShopController::class, 'edit'])->name(
      'shops.edit'
    );
    Route::post('update/{shop}', [ShopController::class, 'update'])->name(
      'shops.update'
    );
  });
// ここまで

Route::get('/dashboard', function () {
  return view('owner.dashboard');
})
  ->middleware(['auth:owners'])
  ->name('dashboard'); // 認証しているかどうか

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

Route::middleware('auth:owners')->group(function () {
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

- `app/Http/Controllers/Owner/ShopController.php`を編集<br>

```php:ShopController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:owners');
  }

  public function index()
  {
    $ownerId = Auth::id();
    $shops = Shop::where('owner_id', $ownerId)->get();

    return view('owner.shops.index', compact('shops'));
  }

  public function edit($id)
  {
  }

  public function update(Request $request, $id)
  {
  }
}
```

- `$ mkdir resources/views/owner/shops && touch $_/{index.blade.php,edit.blade.php}`を実行<br>

- `resources/views/owner/shops/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
  <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ __('Dashboard') }}
      </h2>
  </x-slot>

  <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6 bg-white border-b border-gray-200">
                  @foreach($shops as $shop)
                    {{ $shop->name }}
                  @endforeach
              </div>
          </div>
      </div>
  </div>
</x-app-layout>
```

- `resourcesivew/owner/shops/edit.blade.php`を編集<br>

```php:edit.blade.php
<x-app-layout>
  <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ __('Dashboard') }}
      </h2>
  </x-slot>

  <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6 bg-white border-b border-gray-200">
                  You're logged in!
              </div>
          </div>
      </div>
  </div>
</x-app-layout>
```

- `resources/views/layouts/owner-navigation.blade.php`を編集<br>

```php:owner-navigation.blade.php
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    // 編集
                    <div class="w-12">
                        <a href="{{ route('owner.dashboard') }}">
                            <x-application-logo class="block h-10 w-auto fill-current text-gray-600" />
                        </a>
                    </div>
                    // ここまで
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    // 追加
                    <x-nav-link :href="route('owner.shops.index')" :active="request()->routeIs('owner.shops.index')">
                        店舗情報
                    </x-nav-link>
                    // ここまで
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
                        <form method="POST" action="{{ route('owner.logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('owner.logout')" onclick="event.preventDefault();
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
            <x-responsive-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            // 追加
            <x-responsive-nav-link :href="route('owner.shops.index')" :active="request()->routeIs('owner.shops.index')">
                店舗情報
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
                <form method="POST" action="{{ route('owner.logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('owner.logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
```
