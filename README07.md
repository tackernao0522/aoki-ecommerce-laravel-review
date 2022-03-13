## 41 コントローラ追加修正 その 1

### 7. コントローラ&ブレード作成

LaravelBreeze インストール時のファイルをコピーして修正<br>

`App/Http/Controllers/Auth`<br>

`resources/views/auth`<br>

2. ルート設定の残り<br>

`middleware('auth')->middelware('auth:owners')`<br>

### ハンズオン

- `routes/owner.php`を編集<br>

```php:owner.php
<?php

use App\Http\Controllers\Owner\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Owner\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Owner\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Owner\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Owner\Auth\NewPasswordController;
use App\Http\Controllers\Owner\Auth\PasswordResetLinkController;
use App\Http\Controllers\Owner\Auth\RegisteredUserController;
use App\Http\Controllers\Owner\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('welcome');
});

// 編集
Route::get('/dashboard', function () {
  return view('dashboard');
})
  ->middleware(['auth:owners'])
  ->name('dashboard'); // 認証しているかどうか

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
Route::middleware('auth:owners')->group(function () {
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

- `app/Http/Controllers/User`ディレクトリを作成<br>

* `app/Http/Controllers/Auth`ディレクトリを`app/Http/Controllers/User`ディレクトリに移動<br>

- `app/Http/Controllers/Owner`ディレクトリを作成<br>

* `app/Http/Controllers/User`ディレクトリの中身のファイルを全て`app/Http/Controllers/Owner`にコピー<br>

- `app/Http/Controllers/Admin`ディレクトリを作成<br>

- `$ cp -r app/Http/Controllers/User/Auth app/Http/Controllers/Admin/Auth`を実行<br>

### 7-1. コントローラ

コード編集（user, owner, admin の情報を追記）<br>

namespace を合わせる<br>

`view('login')->view('owner.login')`<br>

```
ROuteServiceProvider::Home->
RouteServiceProvider::OWNER_HOME

Auth::logout()->Auth::guard('owners')->logout();
```

### ハンズオン

- `app/Http/Controllers/Owner/Auth/AuthenticatedSessionController.php`を編集<br>

```php:AuthenticatedSessionController.php
<?php

namespace App\Http\Controllers\Owner\Auth; // 編集

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
    return view('owner.auth.login');
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

    return redirect()->intended(RouteServiceProvider::OWNER_HOME); // 編集
  }

  /**
   * Destroy an authenticated session.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Request $request)
  {
    Auth::guard('owners')->logout(); // 編集

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/owner'); // 編集
  }
}
```

- `app/Http/Controllers/Owner/Auth/ConfirmablePasswordController.php`を編集<br>

```php:ConfirmablePasswordController.php
<?php

namespace App\Http\Controllers\Owner\Auth; // 編集

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
    return view('owner.auth.confirm-password'); // 編集
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
      // 編集
      !Auth::guard('owners')->validate([
        'email' => $request->user()->email,
        'password' => $request->password,
      ])
    ) {
      throw ValidationException::withMessages([
        'password' => __('auth.password'),
      ]);
    }

    $request->session()->put('auth.password_confirmed_at', time());

    return redirect()->intended(RouteServiceProvider::OWNER_HOME); // 編集
  }
}
```

- `app/Http/Controllers/Owner/Auth/EmailVerificationNotificationController.php`を編集<br>

```php:mailVerificationNotificationController.php
<?php

namespace App\Http\Controllers\Owner\Auth; // 編集

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
      return redirect()->intended(RouteServiceProvider::OWNER_HOME); // 編集
    }

    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
  }
}
```

- `app/Http/Controllers/Owner/Auth/EmailVerificationPromptController.php`を編集<br>

```php:EmailVerificationPromptController.php
<?php

namespace App\Http\Controllers\Owner\Auth; // 編集

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
      ? redirect()->intended(RouteServiceProvider::OWNER_HOME) // 編集
      : view('owner.auth.verify-email'); // 編集
  }
}
```

- `app/Http/Controllers/Owner/Auth/NewPasswordController.php`を編集<br>

```php:NewPasswordController.php
<?php

namespace App\Http\Controllers\Owner\Auth; // 編集

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
    return view('owner.auth.reset-password', ['request' => $request]); // 編集
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
        ->route('owner.login') // 編集
        ->with('status', __($status))
      : back()
        ->withInput($request->only('email'))
        ->withErrors(['email' => __($status)]);
  }
}
```

- `app/Http/Controllers/Owner/Auth/PasswordResetLinkController.php`を編集<br>

```php:PasswordResetLinkController.php
<?php

namespace App\Http\Controllers\Owner\Auth; // 編集

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
    return view('owner.auth.forgot-password'); // 編集
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

7-1-3. コントローラ

`RegisteredUserController`にはモデル読み込み、バリデーション設定もあるので注意<br>

`Use App\Models\User;` -> 'Owner`や`Admin`に変更<br>

```
$request->validate([
  'email' => 'required|string|email|max:255|unique:users', -> unique:owners, adminsに変更
]);
```

### ハンズオン

- `app/Http/Controllers/Owner/Auth/RegisteredUserController.php`を編集<br>

```php:RegisteredUserController.php
<?php

namespace App\Http\Controllers\Owner\Auth; // 編集

use App\Http\Controllers\Controller;
use App\Models\Owner; // 編集
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
    return view('owner.auth.register'); // 編集
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
      'email' => ['required', 'string', 'email', 'max:255', 'unique:owners'], // 編集
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = Owner::create([
      // 編集
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));

    Auth::login($user);

    return redirect(RouteServiceProvider::OWNER_HOME); // 編集
  }
}
```

- `app/Http/Controllers/Owner/Auth/VerifyEmailController.php`を編集<br>

```php:VerifyEmailController.php
<?php

namespace App\Http\Controllers\Owner\Auth; // 編集

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
        RouteServiceProvider::OWNER_HOME . '?verified=1' // 編集
      );
    }

    if ($request->user()->markEmailAsVerified()) {
      event(new Verified($request->user()));
    }

    return redirect()->intended(
      RouteServiceProvider::OWNER_HOME . '?verified=1' // 編集
    );
  }
}
```
