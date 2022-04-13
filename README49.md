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
