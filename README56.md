## 155 CartService の作成

### 商品購入後の流れ

1. カート情報を取得<br>
2. カートから商品を削除<br>
3. ユーザー向けのメール(Job)<br>
4. オーナー向けのメール(複数 Job)<br>

カート情報から下記を取得<br>
(商品情報、在庫、オーナー(名前・メールアドレス)->コード量が増えるので `App\Services\CartService@getItemsIncart`を作成)<br>

### CartService ファイル

```php:CartService.php
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Cart;

class CartService
{
  public static function getItemsInCart($items)
  {
    $products = []; // 空の配列を準備

    foreach ($items as $item) { // カート内の商品を一つずつ処理
    }
    略(次ページ)
    return $products; // 新しい配列を返す
  }
}
```

### カート情報を元に配列を作成

```php:CartService.php
foreach ($items as $item) { // カート内の商品を一つずつ処理
  $p = Product::findOrFail($item->product_id);
  $owner = $p->shop->owner->select('name', 'email')
      ->first()->toArray(); // オーナー情報
  $values = array_values($owner); // 連想配列の値を取得
  $keys = ['ownerName', 'email'];
  $ownerInfo = array_combine($keys, $values); // オーナー情報のキーを変更
  $product = Product::where('id', $item->product_id)
      ->select('id', 'name', 'price')->get()->toArray(); // 商品情報の入れる toArrayは配列に変換する
  $quantity = Cart::where('product_id', $item->product_id)
      ->select('quantity')->get()->toArray(); // 在庫数の配列
  $result = array_merge($product[0], $ownerInfo, $quantity[0]); // 配列の結合
  array_push($products, $result); // 配列に追加
```

### CartController

checkout メソッドに一旦追加(動作確認のため)<br>

```php:CartController.php
use App\Services\CartService;
略

public function checkout()
{
  $itemsInCart = Cart::where('user_id', $user->id)->get();
  $products = CartService::getItemsInCart($itemsInCart);
  略
}
```

### ハンズオン

- `$ touch app/Services/CartService.php`を実行<br>

- `app/Services/CartService.php`を編集<br>

```php:CartService.php
<?php

namespace App\Services;

class CartService
{
  public static function getItemsInCart($items)
  {
    $products = [];

    dd($items);
    foreach ($items as $item) {
      // カート内の商品を一つずつ処理
    }

    return $products;
  }
}
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
use App\Services\CartService;

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
    // 追加
    ////
    $items = Cart::where('user_id', Auth::id())->get();
    $products = CartService::getItemsInCart($items);
    ////
    // ここまで

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
      'success_url' => route('user.cart.success'),
      'cancel_url' => route('user.cart.cancel'),
    ]);

    $publicKey = env('STRIPE_PUBLIC_KEY');

    return view('user.checkout', compact('session', 'publicKey'));
  }

  public function success()
  {
    Cart::where('user_id', Auth::id())->delete();

    return redirect()->route('user.items.index');
  }

  public function cancel()
  {
    $user = User::findOrFail(Auth::id());

    foreach ($user->products as $product) {
      Stock::create([
        'product_id' => $product->id,
        'type' => \Constant::PRODUCT_LIST['add'],
        'quantity' => $product->pivot->quantity,
      ]);
    }

    return redirect()->route('user.cart.index');
  }
}
```

## 156 カート情報から新しく配列をつくる

- `app/Services/CartService.php`を編集<br>

```php:CartService.php
<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;

class CartService
{
  public static function getItemsInCart($items)
  {
    $products = [];

    // dd($items);
    foreach ($items as $item) {
      // カート内の商品を一つずつ処理
      $p = Product::findOrFail($item->product_id);
      $owner = $p->shop->owner
        ->select('name', 'email')
        ->first()
        ->toArray();
      $values = array_values($owner);
      $keys = ['ownerName', 'email'];
      $ownerInfo = array_combine($keys, $values);
      // dd($ownerInfo);
      $product = Product::where('id', $item->product_id)
        ->select('id', 'name', 'price')
        ->get()
        ->toArray();
      $quantity = Cart::where('product_id', $item->product_id)
        ->select('quantity')
        ->get()
        ->toArray();
      // dd($ownerInfo, $product, $quantity);
      $result = array_merge($product[0], $ownerInfo, $quantity[0]);
      // dd($result);
      array_push($products, $result);
    }
    // dd($products);
    return $products;
  }
}
```
