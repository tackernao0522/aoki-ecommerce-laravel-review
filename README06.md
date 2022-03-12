## 36 RouteServiceProvider

### 3. ルートサービスプロバイダ設定

`App/Providers/RouteServiceProvider.php`<br>
Owner, Admin それぞれホーム URL を設定<br>

```php:RouteServiceProvider.php
public const OWNER_HOME = '/owner/dashboard';
public const ADMIN_HOME = '/admin/dashboard';
```

- 参考: https://readouble.com/laravel/8.x/ja/routing.html <br>

### ハンズオン

`app/Providers/RouteServiceProvider.php`を編集<br>

```php:RouteServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
  /**
   * The path to the "home" route for your application.
   *
   * This is used by Laravel authentication to redirect users after login.
   *
   * @var string
   */
  public const HOME = '/dashboard';
  public const OWNER_HOME = '/owner/dashboard';
  public const ADMIN_HOME = '/admin/dashboard';

  /**
   * The controller namespace for the application.
   *
   * When present, controller route declarations will automatically be prefixed with this namespace.
   *
   * @var string|null
   */
  // protected $namespace = 'App\\Http\\Controllers';

  /**
   * Define your route model bindings, pattern filters, etc.
   *
   * @return void
   */
  public function boot()
  {
    $this->configureRateLimiting();

    $this->routes(function () {
      Route::prefix('api')
        ->middleware('api')
        ->namespace($this->namespace)
        ->group(base_path('routes/api.php'));

      Route::prefix('admin')
        ->as('admin.')
        ->middleware('web')
        ->namespace($this->namespace)
        ->group(base_path('routes/admin.php'));

      Route::prefix('owner')
        ->as('owner.')
        ->middleware('web')
        ->namespace($this->namespace)
        ->group(base_path('routes/owner.php'));

      Route::prefix('/')
        ->as('user.')
        ->middleware('web')
        ->namespace($this->namespace)
        ->group(base_path('routes/web.php'));
    });
  }

  /**
   * Configure the rate limiters for the application.
   *
   * @return void
   */
  protected function configureRateLimiting()
  {
    RateLimiter::for('api', function (Request $request) {
      return Limit::perMinute(60)->by(
        optional($request->user())->id ?: $request->ip()
      );
    });
  }
}
```

## 37 Guard 設定 config/auth.php

### 4. ガード設定

Laravel 標準の機能<br>

`config/auth.php`<br>

guards ・・ 今回は session<br>
Providers ・・ 今回は Eloquent(モデル)<br>
Passwordreset をそれぞれ設定<br>

参考記事: https://qiita.com/tomoeine/items/40a966bf3801633cf90f <br>
参考: https://readouble.com/laravel/8.x/ja/authentication.html <br>

```php:auth.php
'guards' => [
  'guard-name' => [
    'driver' => 'session',
    'provider' => 'users',
  ],
],
```

```
Route::get('test', function() {})->middleware('auth:guard-name');
ルートで auth:ガード名 で認証されたユーザーだけにアクセス許可
```

### ハンズオン

- `config/auth.php`を編集<br>

```php:auth.php
<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

  'defaults' => [
    'guard' => 'users',
    'passwords' => 'users',
  ],

  /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

  'guards' => [
    'web' => [
      'driver' => 'session',
      'provider' => 'users',
    ],

    'users' => [
      'driver' => 'session',
      'provider' => 'users',
    ],

    'owners' => [
      'driver' => 'session',
      'provider' => 'owners',
    ],

    'admin' => [
      'driver' => 'session',
      'provider' => 'admin',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

  'providers' => [
    'users' => [
      'driver' => 'eloquent',
      'model' => App\Models\User::class,
    ],

    'owners' => [
      'driver' => 'eloquent',
      'model' => App\Models\Owner::class,
    ],

    'admin' => [
      'driver' => 'eloquent',
      'model' => App\Models\Admin::class,
    ],

    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

  'passwords' => [
    'users' => [
      'provider' => 'users',
      'table' => 'password_resets',
      'expire' => 60,
      'throttle' => 60,
    ],

    'owners' => [
      'provider' => 'owners',
      'table' => 'owner_password_resets',
      'expire' => 60,
      'throttle' => 60,
    ],

    'admin' => [
      'provider' => 'admin',
      'table' => 'admin_password_resets',
      'expire' => 60,
      'throttle' => 60,
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

  'password_timeout' => 10800,
];
```

## 38 Middleware/Authenticate

### 5. Middleware 設定

参考: https://readouble.com/laravel/8.x/ja/facades.html <br>

`Middleware/Authenticate.php`<br>

```php:Authenticate.php
// ユーザーが未認証の場合のリダイレクト処理

// URLによって条件分岐
if (Route::is('user.*')) {
  return route($this->user_route);
}
```

API マニュアル<br>
https://laravel.com/api/8.x/Illuminate/Routing/Router.html <br>

`Middleware/RedirectIfAuthenticated.php`<br>

```php:RedirectIfAuthenticated.php
// ログイン済みユーザーがアクセスしてきたらリダイレクト処理

Auth::guard(self::GUARD_USER)->check();
// ガード設定対象のユーザーか

if ($request->routeIs('user.*')) {
}
// 受信リクエストが名前付きルートに一致するか
```

### ハンズオン

- `app/Http/Middleware/Authenticate.php`を編集<br>

```php:Authenticate.php
<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
  protected $user_route = 'user.login';
  protected $owner_route = 'owner.login';
  protected $admin_route = 'admin.login';

  /**
   * Get the path the user should be redirected to when they are not authenticated.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return string|null
   */
  protected function redirectTo($request)
  {
    if (!$request->expectsJson()) {
      if (Route::is('owner.*')) {
        return route($this->owner_route);
      } elseif (Route::is('admin.*')) {
        return route($this->admin_route);
      } else {
        return route($this->user_route);
      }
    }
  }
}
```

## 39 Middleware/RedirectIfAuthenticated

### 5. Middleware 設定

参考: https://readouble.com/laravel/8.x/ja/authentication.html <br>
参考: https://readouble.com/laravel/8.x/ja/requests.html <br>

`Middleware/RedirectIfAuthenticated.php`<br>

```php:RedirectIfAuthenticated.php
// ログイン済みユーザーがアクセスしてきたらリダイレクト処理

Auth::guard(self::GUARD_USER)->check();
// ガード設定対象のユーザーか

if ($request->routeIs('user.*')) {
}
// 受信れクエストが名前付きルートに一致するか
```

### ハンズオン

- `app/Http/Middleware/RedirectIfAuthenticated.php`を編集<br>

```php:RedirectIfAuthenticated.php
<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
  private const GUARD_USER = 'users';
  private const GUARD_OWNER = 'owners';
  private const GUARD_ADMIN = 'admin';

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @param  string|null  ...$guards
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next, ...$guards)
  {
    // $guards = empty($guards) ? [null] : $guards;

    // foreach ($guards as $guard) {
    //     if (Auth::guard($guard)->check()) {
    //         return redirect(RouteServiceProvider::HOME);
    //     }
    // }

    if (Auth::guard(self::GUARD_USER)->check() && $request->routeIs('user.*')) {
      return redirect(RouteServiceProvider::HOME);
    }

    if (
      Auth::guard(self::GUARD_OWNER)->check() &&
      $request->routeIs('owner.*')
    ) {
      return redirect(RouteServiceProvider::OWNER_HOME);
    }

    if (
      Auth::guard(self::GUARD_ADMIN)->check() &&
      $request->routeIs('admin.*')
    ) {
      return redirect(RouteServiceProvider::ADMIN_HOME);
    }

    return $next($request);
  }
}
```
