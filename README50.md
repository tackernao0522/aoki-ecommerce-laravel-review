## 131 Cart 商品を削除

### カート内を削除

ルート<br>

```php:web.php
Route::prefix('cart')->middleware('auth:users')->group(function () {
  Route::post('delete/{item}', [CartController::class, 'delete'])->name('cart.delete');
}):
```

コントローラ<br>

```php:CartControleler.php
public function delete()
{
  Cart::where('product_id', $id)
    ->where('user_id', Auth::id())->delete();

  return redirect()->route('user.cart.index');
}
```

ビュー側<br>

```php:cart.blade.php
<form method="post" action="{{ route('user.cart.delete', $product->id) }}">
  @csrf
  <button></button>
</form>
```

アイコン heroicon https://heroicons.com/ <br>

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
  // 追記
  Route::get('show/{item}', [ItemController::class, 'show'])->name(
    'items.show'
  );
});

Route::prefix('cart')
  ->middleware('auth:users')
  ->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('add', [CartController::class, 'add'])->name('cart.add');
    Route::post('delete/{item}', [CartController::class, 'delete'])->name(
      'cart.delete'
    );
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

    // dd($products, $totalPrice);

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

  // 追記
  public function delete($id)
  {
    Cart::where('product_id', $id)
      ->where('user_id', Auth::id())
      ->delete();

    return redirect()->route('user.cart.index');
  }
}
```

- `resources/views/user/cart.blade.php`を編集<br>

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
                                <div class="md:w-2/12">
                                    // 編集
                                    <form action="{{ route('user.cart.delete', $product->id) }}" method="post">
                                        @csrf
                                        <button>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    // ここまで
                                </div>
                            </div>
                        @endforeach
                        合計金額: {{ $totalPrice }}<span class="text-sm text-gray-700">円(税込)</span>
                    @else
                        カートに商品が入っていません。
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

## 132 Stripe 概要・登録

### Sripe

API 型決済ライブラリ 手数料 3.6%<br>

テストモードあり<br>
会員登録後 API キー発行<br>

https://stripe.com/jp <br>

https://stripe.com/docs <br>

### ハンズオン

`.env`を編集<br>

```:.env
APP_NAME=Umarche
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

# 追加
STRIPE_PUBLIC_KEY="pk_test_51Jk36ZGcUjujaPiNljxzO6TsjfviPEBsdqoeLLR4DHbHwuPFzAMAnYbupIWqW7UuTiGzlZ1nhTsr5yeq6ZYc6awt00r5a0oGz7"
STRIPE_SECRET_KEY="sk_test_51Jk36ZGcUjujaPiNikoJxZdv1e1xtTpHswnQGPYScnP0j7s4fwaKmYiJ5PRgPoNZxmTMxkoLKjyZyFp5IbTvDh8R008I63diwi"
```

## 133 Stripe ライブラリ〜コントローラ その 1

### Stripe の使用方法

Laravel Casher (定期支払い向け)<br>

Stripe が発行しているライブラリ<br>
`composer require stripe/stripe-php`<br>
https://github.com/stripe/stripe-php <br>

### Stripe 決済処理

ルーティング<br>

```php:web.php
Route::prefix('cart')
  ->middleware('auth:users')
  ->group(function () {
    Route::get('checkout', [CartController::class, 'checkout'])->name(
      'cart.checkout'
    );
  });
```

### Stripe 決済処理 コントローラ

Stripe に渡すパラメータを設定<br>
https://stripe.com/docs/api/checkout/sessions/create <br>

```php:CartController.php
public function checkout()
{
  $user = User::findOrFail(Auth::id());

  $line_items = [];

  foreach($user->products as $product) {
    $line_item = [
      'name' => $product->name,
      'description' => $product->information,
      'amount' => $product->price,
      'currency' => 'jpy',
      'quantity' => $product->pivot->quantity,
    ];
    array_push($line_items, $line_item);
  }
}
```

### ハンズオン

- `$ composer require stripe/stripe-php`を実行<br>

* `routes/web.php`を編集<br>

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
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('add', [CartController::class, 'add'])->name('cart.add');
    Route::post('delete/{item}', [CartController::class, 'delete'])->name(
      'cart.delete'
    );
    // 追記
    Route::get('checkout', [CartController::class, 'checkout'])->name(
      'cart.checkout'
    );
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

    // dd($products, $totalPrice);

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

  public function delete($id)
  {
    Cart::where('product_id', $id)
      ->where('user_id', Auth::id())
      ->delete();

    return redirect()->route('user.cart.index');
  }

  // 追記
  public function checkout()
  {
    $user = User::findOrFail(Auth::id());
    $products = $user->products;

    $lineItems = [];

    foreach ($products as $product) {
      $lineItem = [
        'name' => $product->name,
        'description' => $product->information,
        'amount' => $product->price,
        'currency' => 'jpy',
        'quantity' => $product->pivot->quantity,
      ];
      array_push($lineItems, $lineItem);
    }
    dd($lineItems);
  }
}
```
