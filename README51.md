## 134 Sripe コントローラ その 2

### Stripe 決済処理 コントローラ

https://stripe.com/docs/checkout/integration-builder <br>

```php:CartController.php
public function checkout()
{
  略
  \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    $session = \Stripe\Checkout\Session::create([
      'payment_method_types' => ['card'],
      'line_items' => [$line_items],
      'mode' => 'payment',
      'success_url' => route('user.items.index'),
      'cancel_url' => route('user.cart.index ),
    ]);

    $publicKey = env('STRIPE_PUBLIC_KEY');

    return view('user.checkout', compact('session', 'publicKey'));
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
    // dd($lineItems);

    // 追加
    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    $session = \Stripe\Checkout\Session::create([
      'payment_method_types' => ['card'],
      'line_items' => [$lineItems],
      'mode' => 'payment',
      'success_url' => route('user.items.index'),
      'cancel_url' => route('user.cart.index'),
    ]);

    $publicKey = env('STRIPE_PUBLIC_KEY');

    return view('user.checkout', compact('session', 'publicKey'));
    // ここまで
  }
}
```

## 135 Stripe 前の在庫処理

### Stripe 決済処理 在庫処理

`購入ボタン`---`Sripe決済`---この間に在庫情報が変わる可能性-->`在庫を減らす`--->カード情報入力で時間がかかる事前に在庫を保持<br>

`購入ボタン`---`在庫保持`---`Sripe決済`---この間に在庫情報が変わる可能性-->`在庫確定`---><br>

### Stripe 決済処理 コントローラ 3

在庫確認し決済前に在庫を減らしておく<br>

```php:CartController.php
$line_items = [];
foreach($products as $product) {
  $quantity = '';
  $quantity = Stock::where('product_id', $product->id)->sum('quantity');

  if ($product->pivot->quantity > $quantity) {
    return redirect()->route('user.cart.index);
  } else {
    略
  }
}
```

### Stripe 決済処理 コントローラ 4

```php:CartController.php
foreach ($products as $product) {
  Stock::create([
    'product_id' => $product->id,
    'type' => \Constant::PRODUCT_LIST['reduce'],
    'quantity' => $product->pivot->quantity * -1,
  ]);
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
use App\Models\Stock;
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

  public function checkout()
  {
    $user = User::findOrFail(Auth::id());
    $products = $user->products;

    $lineItems = [];

    // 編集
    foreach ($products as $product) {
      $quantity = '';
      $quantity = Stock::where('product_id', $product->id)->sum('quantity');

      if ($product->pivot->quantity > $quantity) {
        return redirect()->route('user.cart.index');
      } else {
        $lineItem = [
          'name' => $product->name,
          'description' => $product->information,
          'amount' => $product->price,
          'currency' => 'jpy',
          'quantity' => $product->pivot->quantity,
        ];
        array_push($lineItems, $lineItem);
      }
    }
    // dd($lineItems);
    foreach ($products as $product) {
      Stock::create([
        'product_id' => $product->id,
        'type' => \Constant::PRODUCT_LIST['reduce'],
        'quantity' => $product->pivot->quantity * -1,
      ]);
    }
    // ここまで

    dd('test');

    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    $session = \Stripe\Checkout\Session::create([
      'payment_method_types' => ['card'],
      'line_items' => [$lineItems],
      'mode' => 'payment',
      'success_url' => route('user.items.index'),
      'cancel_url' => route('user.cart.index'),
    ]);

    $publicKey = env('STRIPE_PUBLIC_KEY');

    return view('user.checkout', compact('session', 'publicKey'));
  }
}
```

### 136 checkout へのボタン追加

### Stripe 決済処理 ビュー 1

```php:cart.blade.php
<div class="my-2">
  小計: {{ number_format($totalPrice) }}<span class="text-sm text-gray-700">円(税込)</span>
</div>
<div>
  <button onclick="location.href='{{ route('user.cart.checkout') }}'">
    購入する
  </button>
</div>
```

### ハンズオン

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
                                    <form action="{{ route('user.cart.delete', $product->id) }}" method="POST">
                                        @csrf
                                        <button>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                        // 編集
                        <div class="my-2">
                            小計: {{ number_format($totalPrice) }}<span class="text-sm text-gray-700">円(税込)</span>
                        </div>
                        <div>
                            <button class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded" onclick="location.href='{{ route('user.cart.checkout') }}'">
                                購入する
                            </button>
                        </div>
                        // ここまで
                    @else
                        カートに商品が入っていません。
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### 137 Stripe Checkout・決済処理

### Stripe 決済処理 ビュー 2

https://stripe.com/docs/checkout/integration-builder <br>

`user/checkout.blade.php`<br>

```php:checkout.blade.php
<p>決済ページへリダイレクトします。</p>
<script src="https://js.stripe.com/v3/"></script>
<script>
    const publicKey = '{{ $publicKey }}'
    const stripe = Stripe(publicKey)

    window.onload = function() {
      stripe.redirectToCheckout({
        sessionId: '{{ $session->id }}'
      }).then(function (result) {
        window.location.href = {{ route('user.cart.index') }}'
      })
    }
</script>
```

### ハンズオン

- `$ touch resources/views/user/checkout.blade.php`を実行<br>

* `resources/views/user/checkout.blade.php`を編集<br>

```php:checkout.blade.php
<p>決済ページへリダイレクトします。</p>
<script src="https://js.stripe.com/v3/"></script>
<script>
    const publicKey = '{{ $publicKey }}'
    const stripe = Stripe(publicKey)

    window.onload = function() {
        stripe.redirectToCheckout({
            sessionId: '{{ $session->id }}'
        }).then(function(result) {
            window.location.href = '{{ route('user.cart.index') }}'
        })
    }
</script>
```

## 138 Stripe 決済成功時の処理

### Stripe 決済処理 成功時

カートから商品を削除<br>

ルート<br>

```php:web.php
Route::prefix('cart')
  ->middleware('auth:users')
  ->group(function () {
    Route::get('success', [CartController::class, 'success'])->name(
      'cart.success'
    );
  });
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
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('add', [CartController::class, 'add'])->name('cart.add');
    Route::post('delete/{item}', [CartController::class, 'delete'])->name(
      'cart.delete'
    );
    Route::get('checkout', [CartController::class, 'checkout'])->name(
      'cart.checkout'
    );
    // 追加
    Route::get('success', [CartController::class, 'success'])->name(
      'cart.success'
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
use App\Models\Stock;
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

  public function checkout()
  {
    $user = User::findOrFail(Auth::id());
    $products = $user->products;

    $lineItems = [];

    foreach ($products as $product) {
      $quantity = '';
      $quantity = Stock::where('product_id', $product->id)->sum('quantity');

      if ($product->pivot->quantity > $quantity) {
        return redirect()->route('user.cart.index');
      } else {
        $lineItem = [
          'name' => $product->name,
          'description' => $product->information,
          'amount' => $product->price,
          'currency' => 'jpy',
          'quantity' => $product->pivot->quantity,
        ];
        array_push($lineItems, $lineItem);
      }
    }
    // dd($lineItems);
    foreach ($products as $product) {
      Stock::create([
        'product_id' => $product->id,
        'type' => \Constant::PRODUCT_LIST['reduce'],
        'quantity' => $product->pivot->quantity * -1,
      ]);
    }

    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    $session = \Stripe\Checkout\Session::create([
      'payment_method_types' => ['card'],
      'line_items' => [$lineItems],
      'mode' => 'payment',
      // 編集 route('user.cart.success')に変更
      'success_url' => route('user.cart.success'),
      'cancel_url' => route('user.cart.index'),
    ]);

    $publicKey = env('STRIPE_PUBLIC_KEY');

    return view('user.checkout', compact('session', 'publicKey'));
  }

  // 追記
  public function success()
  {
    Cart::where('user_id', Auth::id())->delete();

    return redirect()->route('user.items.index');
  }
}
```
