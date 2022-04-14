## 128 Cart 保存処理

### カートに追加

`CartController.php`<br>

```php:CartController.php
public function add(Request $request)
{
  $itemInCart = Cart::where('user_id', Auth::id())
    ->where('product_id', $request->product_id)->first(); // カートに商品がある確認

    if($itemInCart) {
      $itemInCart->quantity += $request->quantity; // あれば数量を追加
      $itemInCart->save();
    } else {
      Cart::create([ // なければ新規作成
        'user_id' => Auth::id(),
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
      ]);
    }

    return redirect()->route('user.cart.index);
}
```

### ハンズオン

- `app/Http/Controllers/User/CartController.php`を編集<br>

```php:CartController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class CartController extends Controller
{
  public function add(Request $request)
  {
    $itemInCart = Cart::where('user_id', Auth::id())
      ->where('product_id', $request->product_id)
      ->first();

    if ($itemInCart) {
      $itemInCart->quantity += $request->quantity;
      $itemInCart->save();
    } else {
      Cart::create([
        'user_id' => Auth::id(),
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
      ]);
    }

    dd('テスト');
  }
}
```

## 129 Cart index メソッド

### カート内表示

ルート<br>

```php:web.php
Route::prefix('cart')
  ->middleware('auth:users')
  ->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
  });
```

コントローラ<br>

```php:CartController.php
$user = User::findOrFail(Auth::id());
$products = $user->products;
$totalPrice = 0;

foreach ($products as $product) {
  $totoalPrice += $product->price * $product->pivot->quantity;
}
```

### ハンズオン

- `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('user.welcome');
});

Route::middleware('auth:users')->group(function () {
  Route::get('/', [ItemController::class, 'index'])->name('items.index');
  Route::get('show/{item}', [ItemController::class, 'show'])->name(
    'items.show'
  );
});

Route::prefix('cart')
  ->middleware('auth:users')
  ->group(function () {
    // 追加
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('add', [CartController::class, 'add'])->name('cart.add');
  });

// Route::get('/dashboard', function () {
//     return view('user.dashboard');
// })->middleware(['auth:users'])->name('dashboard'); // 認証しているかどうか

Route::get('/component-test1', [
  ComponentTestController::class,
  'showComponent1',
]);
Route::get('/component-test2', [
  ComponentTestController::class,
  'showComponent2',
]);
Route::get('/servicecontainertest', [
  LifeCycleTestController::class,
  'showServiceContainerTest',
]);
Route::get('/serviceprovidertest', [
  LifeCycleTestController::class,
  'showServiceProviderTest',
]);

require __DIR__ . '/auth.php';
```

- `app/Http/Controllers/User/CartController.php`を編集<br>

```php:CartController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\User;

class CartController extends Controller
{
  public function index()
  {
    $user = User::findOrFail(Auth::id());
    $products = $user->products;
    $totalPrice = 0;

    foreach ($products as $product) {
      $totalPrice += $product->price * $product->pivot->quantity;
    }

    dd($products, $totalPrice);

    return view('user.cart.index', compact('products', 'totalPrice'));
  }

  public function add(Request $request)
  {
    $itemInCart = Cart::where('user_id', Auth::id())
      ->where('product_id', $request->product_id)
      ->first();

    if ($itemInCart) {
      $itemInCart->quantity += $request->quantity;
      $itemInCart->save();
    } else {
      Cart::create([
        'user_id' => Auth::id(),
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
      ]);
    }

    return redirect()->route('user.cart.index');
  }
}
```

## 130 Cart View を作成

### カート内を表示

ビュー `user/cart.blade.php`<br>

```php:cart.blade.php
@if(count($products) > 0)
  @foreach($products as $product)
    <div>画像</div>
    <div>{{ $product->name }}</div>
    <div>{{ $product->pivot->quantity }}</div>
    <div>{{ number_format($product->pivot->quantity * $product->price) }}円(税込)</div>
  @endforeach
  合計金額: {{ $totalPrice }}
@else
  カートに商品が入っていません。
@endif
```

### ハンズオン

- `$ touch resources/views/user/cart.blade.php`を実行<br>

* `resources/views/user/cart.blade.php`を編集<br>

```php:cart.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            カート
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (count($products) > 0)
                        @foreach ($products as $product)
                            <div class="md:flex md:items-center mb-2">
                                <div class="md:w-3/12">
                                    <img src="{{ $product->imageFirst->filename !== null ? asset('storage/products/' . $product->imageFirst->filename) : '' }}"
                                        alt="">
                                </div>
                                <div class="md:w-4/12 md:ml-2">{{ $product->name }}</div>
                                <div class="md:w-3/12 flex justify-around">
                                    <div>{{ $product->pivot->quantity }}個</div>
                                    <div>{{ number_format($product->pivot->quantity * $product->price) }}<span
                                            class="text-sm text-gray-700">円(税込)</span></div>
                                </div>
                                <div class="md:w-2/12">削除ボタン</div>
                            </div>
                        @endforeach
                        合計金額: {{ $totalPrice }}<span
                                            class="text-sm text-gray-700">円(税込)</span>
                    @else
                        カートに商品が入っていません。
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

- `resources/views/layouts/user-navigation.blade.php`を編集<br>

```php:views/layouts/user-navigation.blade.php
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <div class="w-12">
                        <a href="{{ route('user.items.index') }}">
                            <x-application-logo class="block h-10 w-auto fill-current text-gray-600" />
                        </a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('user.items.index')" :active="request()->routeIs('user.items.index')">
                        {{ __('ホーム') }}
                    </x-nav-link>
                    // 追加
                    <x-nav-link :href="route('user.cart.index')" :active="request()->routeIs('user.cart.index')">
                        カートを表示
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
                        <form method="POST" action="{{ route('user.logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('user.logout')" onclick="event.preventDefault();
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
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('user.items.index')" :active="request()->routeIs('user.items.index')">
                {{ __('ホーム') }}
            </x-responsive-nav-link>
            // 追加
            <x-responsive-nav-link :href="route('user.cart.index')" :active="request()->routeIs('user.cart.index')">
                カートを表示
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
                <form method="POST" action="{{ route('user.logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('user.logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
```

- `app/Http/Controller/User/CartController.php`を修正<br>

```php:CartController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\User;

class CartController extends Controller
{
  public function index()
  {
    $user = User::findOrFail(Auth::id());
    $products = $user->products;
    $totalPrice = 0;

    foreach ($products as $product) {
      $totalPrice += $product->price * $product->pivot->quantity;
    }

    // dd($products, $totalPrice);

    // 修正 'user.cart'にする
    return view('user.cart', compact('products', 'totalPrice'));
  }

  public function add(Request $request)
  {
    $itemInCart = Cart::where('user_id', Auth::id())
      ->where('product_id', $request->product_id)
      ->first();

    if ($itemInCart) {
      $itemInCart->quantity += $request->quantity;
      $itemInCart->save();
    } else {
      Cart::create([
        'user_id' => Auth::id(),
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
      ]);
    }

    return redirect()->route('user.cart.index');
  }
}
```
