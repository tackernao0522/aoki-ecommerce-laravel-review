## 151 メールの設定・解説

### メールの設定ファイル

`config/mail.php`<br>

メール設定 (smtp (simple mail transfer protocol))<br>

グローバル From アドレス<br>

https://readouble.com/laravel/8.x/ja/mail.html (Mailable の生成)<br>

## 152 テストメールの送信

php artisan make:mail TestMail<br>

App/Mail/TestMail.php が生成<br>

```php:TestMail.php
public function build()
{
  return $this->subject('テスト送信完了') // タイトル
    ->view('emails.test'); // 本文
}
```

### テストメール

コントローラ側<br>

テストとして `User/ItemController@index`<br>

```php:ItemController.php
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

Mail::to(Auth::user()->email)->send(new TestMail());
```

### ハンズオン

- `$ php artisan make:mail TestMail`を実行<br>

* `app/Mail/TestMail.php`を編集<br>

```php:TestMail.php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    // 編集
    return $this->subject('テスト送信完了')->view('emails.test');
  }
}
```

- `$ mkdir resources/views/emails && touch $_/test.blade.php`を実行<br>

* `resources/views/emails/test.blade.php`を編集<br>

```html:test.blade.php
メール本文です
<br />
メール本文です
```

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
    // 追記
    Mail::to(Auth::user()->email)->send(new TestMail());

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
