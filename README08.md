## 42 コントローラ追加修正 その 2

- `app/Http/Controllers/Admin/Auth/AuthenticatedSessionController.php`を編集<br>

```php:AuthenticatedSessionController.php
<?php

namespace App\Http\Controllers\Admin\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
  /**
   * Display the login view.
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    return view('admin.auth.login'); // 編集
  }

  /**
   * Handle an incoming authentication request.
   *
   * @param  \App\Http\Requests\Auth\LoginRequest  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(LoginRequest $request)
  {
    $request->authenticate();

    $request->session()->regenerate();

    return redirect()->intended(RouteServiceProvider::ADMIN_HOME); // 編集
  }

  /**
   * Destroy an authenticated session.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Request $request)
  {
    Auth::guard('admin')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/admin'); // 編集
  }
}
```

- `app/Http/Controllers/Admin/Auth/ConfirmablePasswordController.php`を編集<br>

```php:ConfirmablePasswordController.php
<?php

namespace App\Http\Controllers\Admin\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConfirmablePasswordController extends Controller
{
  /**
   * Show the confirm password view.
   *
   * @return \Illuminate\View\View
   */
  public function show()
  {
    return view('admin.auth.confirm-password'); // 編集
  }

  /**
   * Confirm the user's password.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return mixed
   */
  public function store(Request $request)
  {
    if (
      !Auth::guard('admin')->validate([
        'email' => $request->user()->email,
        'password' => $request->password,
      ])
    ) {
      throw ValidationException::withMessages([
        'password' => __('auth.password'),
      ]);
    }

    $request->session()->put('auth.password_confirmed_at', time());

    return redirect()->intended(RouteServiceProvider::ADMIN_HOME); // 編集
  }
}
```

- `app/Http/Controllers/Admin/Auth/EmailVerificationNotificationController.php`を編集<br>

```php:EmailVerificationNotificationController.php
<?php

namespace App\Http\Controllers\Admin\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
  /**
   * Send a new email verification notification.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    if ($request->user()->hasVerifiedEmail()) {
      return redirect()->intended(RouteServiceProvider::ADMIN_HOME); // 編集
    }

    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
  }
}
```

- `app/Http/Controllers/Admin/Auth/EmailVerificationPromptController.php`を編集<br>

```php:EmailVerificationPromptController.php
<?php

namespace App\Http\Controllers\Admin\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
  /**
   * Display the email verification prompt.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return mixed
   */
  public function __invoke(Request $request)
  {
    return $request->user()->hasVerifiedEmail()
      ? redirect()->intended(RouteServiceProvider::ADMIN_HOME) // 編集
      : view('admin.auth.verify-email'); // 編集
  }
}
```

- `app/Http/Controllers/Admin/Auth/NewPasswordController.php`を編集<br>

```php:NewPasswordController.php
<?php

namespace App\Http\Controllers\Admin\Auth; // 編集

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
  /**
   * Display the password reset view.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\View\View
   */
  public function create(Request $request)
  {
    return view('admin.auth.reset-password', ['request' => $request]); // 編集
  }

  /**
   * Handle an incoming new password request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request)
  {
    $request->validate([
      'token' => ['required'],
      'email' => ['required', 'email'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Here we will attempt to reset the user's password. If it is successful we
    // will update the password on an actual user model and persist it to the
    // database. Otherwise we will parse the error and return the response.
    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function ($user) use ($request) {
        $user
          ->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
          ])
          ->save();

        event(new PasswordReset($user));
      }
    );

    // If the password was successfully reset, we will redirect the user back to
    // the application's home authenticated view. If there is an error we can
    // redirect them back to where they came from with their error message.
    return $status == Password::PASSWORD_RESET
      ? redirect()
        ->route('admin.login') // 編集
        ->with('status', __($status))
      : back()
        ->withInput($request->only('email'))
        ->withErrors(['email' => __($status)]);
  }
}
```

- `app/Http/Controllers/Admin/Auth/PasswordResetLinkController.php`を編集<br>

```php:PasswordResetLinkController.php
<?php

namespace App\Http\Controllers\Admin\Auth; // 編集

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
  /**
   * Display the password reset link request view.
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    return view('admin.auth.forgot-password'); // 編集
  }

  /**
   * Handle an incoming password reset link request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request)
  {
    $request->validate([
      'email' => ['required', 'email'],
    ]);

    // We will send the password reset link to this user. Once we have attempted
    // to send the link, we will examine the response then see the message we
    // need to show to the user. Finally, we'll send out a proper response.
    $status = Password::sendResetLink($request->only('email'));

    return $status == Password::RESET_LINK_SENT
      ? back()->with('status', __($status))
      : back()
        ->withInput($request->only('email'))
        ->withErrors(['email' => __($status)]);
  }
}
```

- `app/Http/Controllers/Admin/Auth/RegisteredUserController.php`を編集<br>

```php:RegisteredUserController.php
<?php

namespace App\Http\Controllers\Admin\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Models\Admin; // 編集
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
  /**
   * Display the registration view.
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    return view('admin.auth.register'); // 編集
  }

  /**
   * Handle an incoming registration request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:admins'], // 編集
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // 編集
    $user = Admin::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));

    Auth::login($user);

    return redirect(RouteServiceProvider::ADMIN_HOME); // 編集
  }
}
```

- `app/Http/Controllers/Admin/Auth/VerifyEmailController.php`を編集<br>

```php:VerifyEmailController.php
<?php

namespace App\Http\Controllers\Admin\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
  /**
   * Mark the authenticated user's email address as verified.
   *
   * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function __invoke(EmailVerificationRequest $request)
  {
    if ($request->user()->hasVerifiedEmail()) {
      return redirect()->intended(
        RouteServiceProvider::ADMIN_HOME . '?verified=1' // 編集
      );
    }

    if ($request->user()->markEmailAsVerified()) {
      event(new Verified($request->user()));
    }

    return redirect()->intended(
      RouteServiceProvider::ADMIN_HOME . '?verified=1' // 編集
    );
  }
}
```

- `routes/admin.php`を編集<br>

```php:admin.php
<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Admin\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('welcome');
});

Route::get('/dashboard', function () {
  return view('dashboard');
})
  ->middleware(['auth:admin']) // 編集
  ->name('dashboard');

Route::middleware('guest')->group(function () {
  Route::get('register', [RegisteredUserController::class, 'create'])->name(
    'register'
  );

  Route::post('register', [RegisteredUserController::class, 'store']);

  Route::get('login', [AuthenticatedSessionController::class, 'create'])->name(
    'login'
  );

  Route::post('login', [AuthenticatedSessionController::class, 'store']);

  Route::get('forgot-password', [
    PasswordResetLinkController::class,
    'create',
  ])->name('password.request');

  Route::post('forgot-password', [
    PasswordResetLinkController::class,
    'store',
  ])->name('password.email');

  Route::get('reset-password/{token}', [
    NewPasswordController::class,
    'create',
  ])->name('password.reset');

  Route::post('reset-password', [NewPasswordController::class, 'store'])->name(
    'password.update'
  );
});

// 編集
Route::middleware('auth:admin')->group(function () {
  Route::get('verify-email', [
    EmailVerificationPromptController::class,
    '__invoke',
  ])->name('verification.notice');

  Route::get('verify-email/{id}/{hash}', [
    VerifyEmailController::class,
    '__invoke',
  ])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

  Route::post('email/verification-notification', [
    EmailVerificationNotificationController::class,
    'store',
  ])
    ->middleware('throttle:6,1')
    ->name('verification.send');

  Route::get('confirm-password', [
    ConfirmablePasswordController::class,
    'show',
  ])->name('password.confirm');

  Route::post('confirm-password', [
    ConfirmablePasswordController::class,
    'store',
  ]);

  Route::post('logout', [
    AuthenticatedSessionController::class,
    'destroy',
  ])->name('logout');
});
```

- `app/Http/Controllers/User/Auth/AuthenticatedSessionController.php`を編集<br>

```php:AuthenticatedSessionController.php
<?php

namespace App\Http\Controllers\User\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
  /**
   * Display the login view.
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    return view('user.auth.login'); // 編集
  }

  /**
   * Handle an incoming authentication request.
   *
   * @param  \App\Http\Requests\Auth\LoginRequest  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(LoginRequest $request)
  {
    $request->authenticate();

    $request->session()->regenerate();

    return redirect()->intended(RouteServiceProvider::HOME);
  }

  /**
   * Destroy an authenticated session.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Request $request)
  {
    Auth::guard('users')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/');
  }
}
```

- `app/Http/Controllers/User/Auth/ConfirmablePasswordController.php`を編集<br>

```php:ConfirmablePasswordController.php
<?php

namespace App\Http\Controllers\User\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConfirmablePasswordController extends Controller
{
  /**
   * Show the confirm password view.
   *
   * @return \Illuminate\View\View
   */
  public function show()
  {
    return view('user.auth.confirm-password'); // 編集
  }

  /**
   * Confirm the user's password.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return mixed
   */
  public function store(Request $request)
  {
    if (
      !Auth::guard('users')->validate([
        // 編集
        'email' => $request->user()->email,
        'password' => $request->password,
      ])
    ) {
      throw ValidationException::withMessages([
        'password' => __('auth.password'),
      ]);
    }

    $request->session()->put('auth.password_confirmed_at', time());

    return redirect()->intended(RouteServiceProvider::HOME);
  }
}
```

- `app/Http/Controllers/User/Auth/EmailVerificationNotificationController.php`を編集<br>

```php:EmailVerificationNotificationController.php
<?php

namespace App\Http\Controllers\User\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
  /**
   * Send a new email verification notification.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    if ($request->user()->hasVerifiedEmail()) {
      return redirect()->intended(RouteServiceProvider::HOME);
    }

    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
  }
}
```

- `app/Http/Controllers/User/Auth/EmailVerificationPromptController.php`を編集<br>

```php:EmailVerificationPromptController.php
<?php

namespace App\Http\Controllers\User\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
  /**
   * Display the email verification prompt.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return mixed
   */
  public function __invoke(Request $request)
  {
    return $request->user()->hasVerifiedEmail()
      ? redirect()->intended(RouteServiceProvider::HOME)
      : view('user.auth.verify-email'); // 編集
  }
}
```

- `app/Http/Controllers/User/Auth/NewPasswordController.php`を編集<br>

```php:NewPasswordController.php
<?php

namespace App\Http\Controllers\User\Auth; // 編集

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
  /**
   * Display the password reset view.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\View\View
   */
  public function create(Request $request)
  {
    return view('user.auth.reset-password', ['request' => $request]); // 編集
  }

  /**
   * Handle an incoming new password request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request)
  {
    $request->validate([
      'token' => ['required'],
      'email' => ['required', 'email'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Here we will attempt to reset the user's password. If it is successful we
    // will update the password on an actual user model and persist it to the
    // database. Otherwise we will parse the error and return the response.
    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function ($user) use ($request) {
        $user
          ->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
          ])
          ->save();

        event(new PasswordReset($user));
      }
    );

    // If the password was successfully reset, we will redirect the user back to
    // the application's home authenticated view. If there is an error we can
    // redirect them back to where they came from with their error message.
    return $status == Password::PASSWORD_RESET
      ? redirect()
        ->route('user.login') // 編集
        ->with('status', __($status))
      : back()
        ->withInput($request->only('email'))
        ->withErrors(['email' => __($status)]);
  }
}
```

- `app/Http/Controllers/User/Auth/PasswordResetLinkController.php`を編集<br>

```php:PasswordResetLinkController.php
<?php

namespace App\Http\Controllers\User\Auth; // 編集

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
  /**
   * Display the password reset link request view.
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    return view('user.auth.forgot-password'); // 編集
  }

  /**
   * Handle an incoming password reset link request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request)
  {
    $request->validate([
      'email' => ['required', 'email'],
    ]);

    // We will send the password reset link to this user. Once we have attempted
    // to send the link, we will examine the response then see the message we
    // need to show to the user. Finally, we'll send out a proper response.
    $status = Password::sendResetLink($request->only('email'));

    return $status == Password::RESET_LINK_SENT
      ? back()->with('status', __($status))
      : back()
        ->withInput($request->only('email'))
        ->withErrors(['email' => __($status)]);
  }
}
```

- `app/Http/Controllers/User/Auth/RegisteredUserController.php`を編集<br>

```php:RegisteredUserController.php
<?php

namespace App\Http\Controllers\User\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
  /**
   * Display the registration view.
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
    return view('user.auth.register'); // 編集
  }

  /**
   * Handle an incoming registration request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));

    Auth::login($user);

    return redirect(RouteServiceProvider::HOME);
  }
}
```

- `app/Http/Controllers/User/Auth/VerifyEmailController.php`を編集<br>

```php:VerifyEmailController.php
<?php

namespace App\Http\Controllers\User\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
  /**
   * Mark the authenticated user's email address as verified.
   *
   * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function __invoke(EmailVerificationRequest $request)
  {
    if ($request->user()->hasVerifiedEmail()) {
      return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
    }

    if ($request->user()->markEmailAsVerified()) {
      event(new Verified($request->user()));
    }

    return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
  }
}
```

- `routes/auth.php`を編集<br>

```php:auth.php
<?php

use App\Http\Controllers\User\Auth\AuthenticatedSessionController;
use App\Http\Controllers\User\Auth\ConfirmablePasswordController;
use App\Http\Controllers\User\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\User\Auth\EmailVerificationPromptController;
use App\Http\Controllers\User\Auth\NewPasswordController;
use App\Http\Controllers\User\Auth\PasswordResetLinkController;
use App\Http\Controllers\User\Auth\RegisteredUserController;
use App\Http\Controllers\User\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
  Route::get('register', [RegisteredUserController::class, 'create'])->name(
    'register'
  );

  Route::post('register', [RegisteredUserController::class, 'store']);

  Route::get('login', [AuthenticatedSessionController::class, 'create'])->name(
    'login'
  );

  Route::post('login', [AuthenticatedSessionController::class, 'store']);

  Route::get('forgot-password', [
    PasswordResetLinkController::class,
    'create',
  ])->name('password.request');

  Route::post('forgot-password', [
    PasswordResetLinkController::class,
    'store',
  ])->name('password.email');

  Route::get('reset-password/{token}', [
    NewPasswordController::class,
    'create',
  ])->name('password.reset');

  Route::post('reset-password', [NewPasswordController::class, 'store'])->name(
    'password.update'
  );
});

// 編集
Route::middleware('auth:users')->group(function () {
  Route::get('verify-email', [
    EmailVerificationPromptController::class,
    '__invoke',
  ])->name('verification.notice');

  Route::get('verify-email/{id}/{hash}', [
    VerifyEmailController::class,
    '__invoke',
  ])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

  Route::post('email/verification-notification', [
    EmailVerificationNotificationController::class,
    'store',
  ])
    ->middleware('throttle:6,1')
    ->name('verification.send');

  Route::get('confirm-password', [
    ConfirmablePasswordController::class,
    'show',
  ])->name('password.confirm');

  Route::post('confirm-password', [
    ConfirmablePasswordController::class,
    'store',
  ]);

  Route::post('logout', [
    AuthenticatedSessionController::class,
    'destroy',
  ])->name('logout');
});
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
  ->middleware(['auth:users']) // 編集
  ->name('dashboard');

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
Route::get('/serviceprovidertest', [
  LifeCycleTestController::class,
  'showServiceProviderTest',
]);

require __DIR__ . '/auth.php';
```
