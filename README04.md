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

## 32 サービスプロバイダー その 2

### サービスプロバイダの生成

- php artisan make:provider SampleServiceProvider<br>
  App/Providers 配下に生成<br>

```
public function register()
{
  サービスを登録するコード
}

public function boot()
{
  全サービスプロバイダー読み込み後に実行したいコード
}
```

### ハンズオン

- `$ php artisan make:provider SampleServiceProvider`を実行<br>

* `app/Proviers/SampleServiceProvider.php`を編集<br>

```php:SampleServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SampleServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   *
   * @return void
   */
  public function register()
  {
    // 編集
    app()->bind('serviceProviderTest', function () {
      return 'サービスプロバイダのテスト';
    });
  }

  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {
    //
  }
}
```

- `config/app.php`を編集<br>

```php:app.php
<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

  'name' => env('APP_NAME', 'Laravel'),

  /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

  'env' => env('APP_ENV', 'production'),

  /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

  'debug' => (bool) env('APP_DEBUG', false),

  /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

  'url' => env('APP_URL', 'http://localhost'),

  'asset_url' => env('ASSET_URL', null),

  /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

  'timezone' => 'Asia/Tokyo',

  /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

  'locale' => 'ja',

  /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

  'fallback_locale' => 'en',

  /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

  'faker_locale' => 'en_US',

  /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

  'key' => env('APP_KEY'),

  'cipher' => 'AES-256-CBC',

  /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

  'providers' => [
    /*
     * Laravel Framework Service Providers...
     */
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    Illuminate\Bus\BusServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
    Illuminate\Cookie\CookieServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Encryption\EncryptionServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Foundation\Providers\FoundationServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Mail\MailServiceProvider::class,
    Illuminate\Notifications\NotificationServiceProvider::class,
    Illuminate\Pagination\PaginationServiceProvider::class,
    Illuminate\Pipeline\PipelineServiceProvider::class,
    Illuminate\Queue\QueueServiceProvider::class,
    Illuminate\Redis\RedisServiceProvider::class,
    Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,

    /*
     * Package Service Providers...
     */

    /*
     * Application Service Providers...
     */
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    // 追記
    App\Providers\SampleServiceProvider::class,
  ],

  /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

  'aliases' => [
    'App' => Illuminate\Support\Facades\App::class,
    'Arr' => Illuminate\Support\Arr::class,
    'Artisan' => Illuminate\Support\Facades\Artisan::class,
    'Auth' => Illuminate\Support\Facades\Auth::class,
    'Blade' => Illuminate\Support\Facades\Blade::class,
    'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
    'Bus' => Illuminate\Support\Facades\Bus::class,
    'Cache' => Illuminate\Support\Facades\Cache::class,
    'Config' => Illuminate\Support\Facades\Config::class,
    'Cookie' => Illuminate\Support\Facades\Cookie::class,
    'Crypt' => Illuminate\Support\Facades\Crypt::class,
    'Date' => Illuminate\Support\Facades\Date::class,
    'DB' => Illuminate\Support\Facades\DB::class,
    'Eloquent' => Illuminate\Database\Eloquent\Model::class,
    'Event' => Illuminate\Support\Facades\Event::class,
    'File' => Illuminate\Support\Facades\File::class,
    'Gate' => Illuminate\Support\Facades\Gate::class,
    'Hash' => Illuminate\Support\Facades\Hash::class,
    'Http' => Illuminate\Support\Facades\Http::class,
    'Js' => Illuminate\Support\Js::class,
    'Lang' => Illuminate\Support\Facades\Lang::class,
    'Log' => Illuminate\Support\Facades\Log::class,
    'Mail' => Illuminate\Support\Facades\Mail::class,
    'Notification' => Illuminate\Support\Facades\Notification::class,
    'Password' => Illuminate\Support\Facades\Password::class,
    'Queue' => Illuminate\Support\Facades\Queue::class,
    'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
    'Redirect' => Illuminate\Support\Facades\Redirect::class,
    // 'Redis' => Illuminate\Support\Facades\Redis::class,
    'Request' => Illuminate\Support\Facades\Request::class,
    'Response' => Illuminate\Support\Facades\Response::class,
    'Route' => Illuminate\Support\Facades\Route::class,
    'Schema' => Illuminate\Support\Facades\Schema::class,
    'Session' => Illuminate\Support\Facades\Session::class,
    'Storage' => Illuminate\Support\Facades\Storage::class,
    'Str' => Illuminate\Support\Str::class,
    'URL' => Illuminate\Support\Facades\URL::class,
    'Validator' => Illuminate\Support\Facades\Validator::class,
    'View' => Illuminate\Support\Facades\View::class,
  ],
];
```

- `app/Http/Controllers/LifeCycleTestController.php`を編集<br>

```php:LifeCycleTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
  public function showServiceProviderTest()
  {
    $encrypt = app()->make('encrypter');
    $password = $encrypt->encrypt('password');

    // 追記
    $sample = app()->make('serviceProviderTest');

    // 編集
    dd($sample, $password, $encrypt->decrypt($password));
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

- キャッシュをクリアしてから https://localhost/serviceprovidertest にアクセスしてみる<br>
