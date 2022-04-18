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

## 157 ユーザー向け商品購入メール

### ユーザー向けにメール送信

ユーザー向け<br>
メールクラス ThanksMail<br>
ジョブクラス SendThnaksMail<br>

メール生成<br>
php artisan make:mail ThanksMail<br>

ジョブ<br>
php artisan make:job SendThanksMail (生成済み)<br>

### ハンズオン

- `$ php artisan make:mail ThanksMail`を実行<br>

### ユーザー情報、商品情報（変数)を受け渡す

`Controller`<br>

```
$user = 略;

job($user, $products);
```

↓<br>

`job`<br>

```
Class {
  public $user;
  public $products;

  public function __construct($user, $products)
  {
    $his->user = $user;
    $this->products = $products;
  }

  public function handle()
  {
    Mail::to($this->user)->send(new ThanksMail($this->products, $this->user));
  }
}
```

↓<br>

`Mailable`<br>

```
Class {
  public $user;
  public $products;

  public function __construct($user, $products)
  {
    $this->user = $user;
    $this->products = $products;
  }

  public function build()
  {
    return $this->view('emails.thanks')
      ->subject('ご購入ありがとうございます。');
  }
}
```

↓<br>

`Blade`<br>

```
{{ $user->name }}

@foreach($products as $product)
  {{ $product['name'] }}
  略
@endforeach
```

https://readouble.com/laravel/8.x/ja/mail.html (ビューデータ)<br>

### ハンズオン

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SendThanksMail;
use App\Mail\TestMail;
use App\Models\PrimaryCategory;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\RequestStack;

class ItemController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:users');

    $this->middleware(function ($request, $next) {
      $id = $request->route()->parameter('item');
      if (!is_null($id)) {
        // null判定
        $itemId = Product::availableItems()
          ->where('products.id', $id)
          ->exists();
        if (!$itemId) {
          abort(404); // 404画面表示
        }
      }

      return $next($request);
    });
  }

  public function index(Request $request)
  {
    // dd($request);
    // 同期的に送信
    // Mail::to(Auth::user()->email)->send(new TestMail());

    // 非同期に送信
    // SendThanksMail::dispatch(); // コメントアウトor 削除

    $categories = PrimaryCategory::with('secondary')->get();

    $products = Product::availableItems()
      ->selectCategory($request->category ?? '0')
      ->searchKeyword($request->keyword)
      ->sortOrder($request->sort)
      ->paginate($request->pagination ?? '20');

    return view('user.index', compact('products', 'categories'));
  }

  public function show($id)
  {
    $product = Product::findOrFail($id);
    $quantity = Stock::where('product_id', $product->id)->sum('quantity');

    if ($quantity > 9) {
      $quantity = 9;
    }

    return view('user.show', compact('product', 'quantity'));
  }
}
```

- `app/Http/Controllers/User/CartController.php`を編集<br>

```php:CartController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SendThanksMail;
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
    ////
    $items = Cart::where('user_id', Auth::id())->get();
    $products = CartService::getItemsInCart($items);
    // 追加
    $user = User::findOrFail(Auth::id());

    SendThanksMail::dispatch($products, $user);
    dd('ユーザーメール送信テスト');
    // ここまで
    ////

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

- `app/Jobs/SendThanksMail.php`を編集<br>

```php:SendThanksMail.php
<?php

namespace App\Jobs;

use App\Mail\TestMail;
use App\Mail\ThanksMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SendThanksMail implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  // 追加
  public $products;
  public $user;

  // 編集
  public function __construct($products, $user)
  {
    $this->products = $products;
    $this->user = $user;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    // Mail::to('takaki55730317@gmail.com')->send(new TestMail());
    // 追加
    Mail::to($this->user)->send(new ThanksMail($this->products, $this->user));
  }
}
```

- `app/Mail/ThanksMail.php`を編集<br>

```php:ThanksMail.php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ThanksMail extends Mailable
{
  use Queueable, SerializesModels;

  public $products;
  public $user;

  public function __construct($products, $user)
  {
    $this->products = $products;
    $this->user = $user;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->view('emails.thanks')->subject(
      'ご購入ありがとうございます。'
    );
  }
}
```

- `$ touch resources/views/emails/thanks.blade.php`を実行<br>

* `resources/views/emails/thanks.blade.php`を編集<br>

```php:thanks.blade.php
<p class="mb-4">{{ $user->name }} 様</p>

<p class="mb-4">下記のご注文ありがとうございました。</p>

商品内容
@foreach ($products as $product)
    <ul class="mb-4">
        <li>商品名: {{ $product['name'] }}</li>
        <li>商品金額: {{ $product['price'] }}円</li>
        <li>商品数: {{ $product['quantity'] }}</li>
        <li>合計金額: {{ number_format($product['price'] * $product['quantity']) }}円</li>
    </ul>
@endforeach
```

- `$ php artisan queue:work`を実行<br>

* 購入するとメールが届く<br>
