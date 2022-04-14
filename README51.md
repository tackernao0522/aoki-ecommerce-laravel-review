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
