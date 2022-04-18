## 158 オーナー向け商品販売メール

### オーナー向けにメール送信 その 1

複数の商品・・オーナーが別-->それぞれにメール送信<br>

メール php artisan make:mail OrderedMail<br>
ジョブ php artisan make:job SendOrderedMail<br>

### オーナー向けにメール送信 その 2

`CartController`<br>

```php:CartController.php
use App\Job\SendOrderedMail;
略

foreach($products as $product) {
  SendOrderedMail::dispatch($product, $user);
}
```

コントローラ-->ジョブ-->メール-->ブレードの流れはユーザー向けと同じ<br>

### ハンズオン

- `$ php artisan make:mail OrderedMail`を実行<br>

* `$ php artisan make:job SendOrderedMail`を実行<br>

- `app/Http/Controllers/User/CartController.php`を編集<br>

```php:CartController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderedMail;
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
    $user = User::findOrFail(Auth::id());

    SendThanksMail::dispatch($products, $user);
    // 追加
    foreach ($products as $product) {
      SendOrderedMail::dispatch($product, $user);
    }
    // ここまで
    dd('ユーザーメール送信テスト');
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

- `app/Jobs/SendOrderedMail.php`を編集<br>

```php:SendOrderedMail.php
<?php

namespace App\Jobs;

use App\Mail\OrderedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderedMail implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $product;
  public $user;

  public function __construct($product, $user)
  {
    $this->product = $product;
    $this->user = $user;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    Mail::to($this->product['email'])->send(
      new OrderedMail($this->product, $this->user)
    );
  }
}
```

- `app/Mail/OrderedMail.php`を編集<br>

```php:OrderedMail.php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderedMail extends Mailable
{
  use Queueable, SerializesModels;

  public $product;
  public $user;

  public function __construct($product, $user)
  {
    $this->product = $product;
    $this->user = $user;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->view('emails.ordered')->subject('商品が注文されました。');
  }
}
```

- `$ touch resources/views/emails/ordered.blade.php`を実行<br>

* `resources/views/emails/orderd.blade.php`を編集<br>

```php:ordered.blade.php
<p class="mb-4">{{ $product['ownerName'] }}様の商品が注文されました。</p>

<div class="mb-4">商品情報</div>
<ul class="mb-4">
    <li>商品名: {{ $product['name'] }}</li>
    <li>商品金額: {{ number_format($product['price']) }}円</li>
    <li>商品数: {{ $product['quantity'] }}</li>
    <li>合計金額: {{ number_format($product['price'] * $product['quantity']) }}円</li>
</ul>

<div class="cmt-4">購入者情報</div>
<ul>
    <li>{{ $user->name }}様</li>
</ul>
```

- `$ php artisan queue:work`を実行<br>

* 購入してみるとオーナー宛にもメールが届く<br>

- `app/Http/Controllers/User/CartController.php`を編集<br>

```php:CartController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderedMail;
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
    // checkoutから移動
    ////
    $items = Cart::where('user_id', Auth::id())->get();
    $products = CartService::getItemsInCart($items);
    $user = User::findOrFail(Auth::id());

    SendThanksMail::dispatch($products, $user);
    foreach ($products as $product) {
      SendOrderedMail::dispatch($product, $user);
    }
    // dd('ユーザーメール送信テスト');
    ////
    // ここまで

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
