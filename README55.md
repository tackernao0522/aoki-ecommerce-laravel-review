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

## 153 非同期処理(キュー&ジョブ)

### 非同期処理

メール送信には時間がかかる<br>

同期処理・・送信してから画面更新<br>

非同期処理・・画面上は更新しつつ、裏側で送信 -> キューで対応<br>

### 非同期処理 簡易図

Queue(キュー)・・待ち行列<br>
Job(ジョブ)・・1 つ 1 つの処理<br>
Worker(ワーカー)・・処理をする人<br>

`Job`->キュー(Queue)` Job``Job``Job `_Worker_<br>

### キューの設定

マイグレーション `failed_jobs_table`<br>

設定ファイル `config/queue.php`<br>

sync・・同期<br>
(他に database, redis, beanstalkd, sqs など)<br>
今回は database に変更<br>

.ev ファイルを下記に書き換える<br>

php artisan config:cache でキャッシュ削除<br>

キューの保存場所が DB になる<br>

### ジョブのテーブル生成

ジョブを保存するテーブルを生成<br>
php artisan queue:table<br>
(これで jobs というテーブルが生成される)<br>

php artisan migrate<br>
(DB 内にテーブル生成)<br>
キューを使うとこのテーブルに未実行のジョブが溜まっていく<br>

### ジョブクラスの作成

ジョブの作成<br>
php artisan make:job SendThanksMail<br>

`App\Jobs\SendThanksMail.php`の中にメール送信設定を追加<br>

```php:SendThanksMail.php
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

public function handle()
{
  Mail::to($this->user)->send(new TestMail());
}
```

## ジョブの dispatch->キュー

`User/ItemController@index`<br>

```php:ItemController.php
use App\Jobs\SendThanksMail;

// キューにジョブを入れて処理(非同期)
SendThanksMail::dispatch();
```

これで非同期処理になる<br>
裏側(画面)はすぐに更新される<br>
裏側でキューにジョブが入っていく<br>
(phpMyAdmin で jobs テーブルを見てみる)<br>

### ハンズオン

- `.env`を編集<br>

```.env
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
<!-- 編集 -->
QUEUE_CONNECTION=database
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

STRIPE_PUBLIC_KEY="pk_test_51Jk36ZGcUjujaPiNljxzO6TsjfviPEBsdqoeLLR4DHbHwuPFzAMAnYbupIWqW7UuTiGzlZ1nhTsr5yeq6ZYc6awt00r5a0oGz7"
STRIPE_SECRET_KEY="sk_test_51Jk36ZGcUjujaPiNikoJxZdv1e1xtTpHswnQGPYScnP0j7s4fwaKmYiJ5PRgPoNZxmTMxkoLKjyZyFp5IbTvDh8R008I63diwi"
```

- `$ php artisan config:cache`を実行<br>

* `$ php artisan queue:table`を実行<br>

- `$ php artisan migrate`を実行<br>

* `$ php artisan make:job SendThanksMail`を実行<br>

- `ItemController`の`Mail::to(Auth::user()->email)->send(new TestMail());`は消しておく<br>

* `app/Jobs/SendThanksMail.php`を編集<br>

```php:SendThanksMail.php
<?php

namespace App\Jobs;

use App\Mail\TestMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendThanksMail implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    // 編集
    Mail::to($this->user)->send(new TestMail());
  }
}
```

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

    // 追加
    // 非同期に送信
    SendThanksMail::dispatch();

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
