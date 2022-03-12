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
