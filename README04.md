## 29 サービスコンテナ その 2

### 依存関係の解決

依存した 2 つのクラス<br>
それぞれインスタンス化後に実行<br>

```
$message = new Message();
$sample = new Sample($message);
$sample->run();

サービスコンテナを使ったパターン
app()->bind('sample', Sample::class);
$sample = app()->make('sample');
$sample->run();
```

`例`<br>

```
class Sample
{
  public $message;
  public function __construnct(Message $message) {
    $this->message = $message;
  }
  public function run() { $this->message->send(); }
}

class Message
{
  public function send() { echo('メッセージ表示'); }
}
```

- `app/Http/Controllers/LifeCycleTestController.php`を編集<br>

```php:LifeCycleTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
  public function showServiceContainerTest()
  {
    app()->bind('lifeCycleTest', function () {
      return 'ライフサイクルテスト';
    });

    $test = app()->make('lifeCycleTest');

    // サービスコンテナなしのパターン
    $message = new Message();
    $sample = new Sample($message);
    $sample->run();

    dd($test, app());
  }
}

class Sample
{
  public $message;

  public function __construct(Message $message)
  {
    $this->message = $message;
  }

  public function run()
  {
    $this->message->send();
  }
}

class Message
{
  public function send()
  {
    echo 'メッセージ表示';
  }
}
```

- https://localhost/servicecontainertest にアクセスしてみる<br>

- `app/Http/Controllers/LifeCycleTestController.php`を編集<br>

```php:LifeCycleTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
  public function showServiceContainerTest()
  {
    app()->bind('lifeCycleTest', function () {
      return 'ライフサイクルテスト';
    });

    $test = app()->make('lifeCycleTest');

    // サービスコンテナなしのパターン
    // $message = new Message();
    // $sample = new Sample($message);
    // $sample->run();

    // サービスコンテナapp()ありのパターン
    app()->bind('sample', Sample::class);
    $sample = app()->make('sample');
    $sample->run();

    dd($test, app());
  }
}

class Sample
{
  public $message;

  public function __construct(Message $message)
  {
    $this->message = $message;
  }

  public function run()
  {
    $this->message->send();
  }
}

class Message
{
  public function send()
  {
    echo 'メッセージ表示';
  }
}
```

## 30 ライフサイクル その 2

- 参考: https://readouble.com/laravel/8.x/ja/lifecycle.html <br>

### 3. Kernel

`例`<br>
`Bootstrap/app.php`<br>
singleton で Kernel をサービスコンテに登録

`Public/index.php`<br>

```
$kernel = $app->make(Kernel::class);
```

## 31 サービスプロバイダー その 1

### サービスプロバイダ(提供者)

`ServiceProvider boot()登録後に実行したい処理`register()登録 -> app()`<br>

サービスコンテナにサービスを登録する仕組み<br>

### サービスプロバイダの読込箇所

```
illuminate\Foundation\Application

registerConfiguredProviders() {
  $providers = Collection::make($this->config['app.providers']);
}
```

- `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('welcome');
});

Route::get('/dashboard', function () {
  return view('dashboard');
})
  ->middleware(['auth'])
  ->name('dashboard'); // 認証しているかどうか

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
// 追記
Route::get('/serviceprovidertest', [
  LifeCycleTestController::class,
  'showServiceProviderTest',
]);

require __DIR__ . '/auth.php';
```

### サービスプロバイダを使ってみる

EncryptionServiceProvider を参考に<br>

使い方<br>

```
$encrypt = app()->make('encrypter');
$password = $encrypt->encrypt('password');
dd($password, $encrypt->decrypt($password));
```

- `app/Http/Controllers/LifeCycleTestController.php`を編集<br>

```php:LifeCycleTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
  // 追記
  public function showServiceProviderTest()
  {
    $encrypt = app()->make('encrypter');
    $password = $encrypt->encrypt('password');
    dd($password, $encrypt->decrypt($password));
  }

  public function showServiceContainerTest()
  {
    app()->bind('lifeCycleTest', function () {
      return 'ライフサイクルテスト';
    });

    $test = app()->make('lifeCycleTest');

    // サービスコンテナなしのパターン
    // $message = new Message();
    // $sample = new Sample($message);
    // $sample->run();

    // サービスコンテナapp()ありのパターン
    app()->bind('sample', Sample::class);
    $sample = app()->make('sample');
    $sample->run();

    dd($test, app());
  }
}

class Sample
{
  public $message;

  public function __construct(Message $message)
  {
    $this->message = $message;
  }

  public function run()
  {
    $this->message->send();
  }
}

class Message
{
  public function send()
  {
    echo 'メッセージ表示';
  }
}
```

- https://localhost/serviceprovidertest にアクセスしてみる<br>
