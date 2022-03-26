## 79 Shop Update の残り

### Shop Update 残りのコード抜粋

```php:ShopController.php
$request->validate([
  'name' => 'required|string|max:50',
  'information' => 'required|string|max:1000',
  'is_selling' => 'required',
]);

$shop = Shop::findOrFail($id);
$shop->name = $request->name;
$shop->information = $request->information;
$shop->is_selling = $request->is_selling;

if (!is_null($imageFile) && imageFile->isValid()) {
  $shop->filename = $fileNamtToStore;
}

$shop->save();

redirect()
  ->route()
  ->with([]); // フラッシュメッセージ
```

### ハンズオン

- `app/Http/Controllers/Owner/ShopController.php`を編集<br>

```php:ShopController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use App\Models\Shop;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:owners');

    $this->middleware(function ($request, $next) {
      // dd($request->route()->parameter('shop')); // 文字列
      // // dd(Auth::id()); // 数字
      // return $next($request);
      $id = $request->route()->parameter('shop'); // shopのid取得
      if (!is_null($id)) {
        // null判定
        $shopsOwnerId = Shop::findOrFail($id)->owner->id;
        $shopId = (int) $shopsOwnerId; // キャスト 文字列ー>数値に型変換
        $ownerId = Auth::id();
        if ($shopId !== $ownerId) {
          // 同じでなかったら
          abort(404); // 404画面表示
        }
      }

      return $next($request);
    });
  }

  public function index()
  {
    // phpinfo();
    // $ownerId = Auth::id();
    $shops = Shop::where('owner_id', Auth::id())->get();

    return view('owner.shops.index', compact('shops'));
  }

  public function edit($id)
  {
    // dd(Shop::findOrFail($id));
    $shop = Shop::findOrFail($id);

    return view('owner.shops.edit', compact('shop'));
  }

  public function update(UploadImageRequest $request, $id)
  {
    $request->validate([
      'name' => 'required|string|max:50',
      'information' => 'required|string|max:1000',
      'is_selling' => 'required',
    ]);

    $imageFile = $request->image;

    // もし空でなかったら及びアップロードできたら
    if (!is_null($imageFile) && $imageFile->isValid()) {
      $fileNameToStore = ImageService::upload($imageFile, 'shops');
    }

    $shop = Shop::findOrFail($id);
    $shop->name = $request->name;
    $shop->information = $request->information;
    $shop->is_selling = $request->is_selling;
    if (!is_null($imageFile) && $imageFile->isValid()) {
      $shop->filename = $fileNameToStore;
    }

    $shop->save();

    return redirect()
      ->route('owner.shops.index')
      ->with([
        'message' => '店舗情報を更新しました。',
        'status' => 'info',
      ]);
  }
}
```

- `resources/views/owner/shops/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    // 追加
                    <x-flash-message status="session('status')" />
                    @foreach ($shops as $shop)
                        <div class="w-1/2 p-4">
                            <a href="{{ route('owner.shops.edit', $shop->id) }}">
                                <div class="border rounded-md p-4">
                                    <div class="mb-4">
                                        @if ($shop->is_selling)
                                            <span class="border p-2 rounded-md bg-blue-400 text-white">販売中</span>
                                        @else
                                            <span class="border p-2 rounded-md bg-red-400 text-white">停止中</span>
                                        @endif
                                    </div>
                                    <div class="text-xl">
                                        {{ $shop->name }}
                                    </div>
                                    // 編集
                                    <x-shop-thumbnail :filename="$shop->filename" />
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

- `resources/views/components/shop-thumbnail.blade.php`を編集<br>

```php:shop-thumbnail.blade.php
<div>
    // 編集
    @if (empty($filename))
        <img src="{{ asset('images/no_image.jpg') }}">
    @else
        <img src="{{ asset('storage/shops/' . $filename) }}">
    @endif
</div>
```

- `resources/views/owner/shops/edit.blade.php`を編集<br>

```php:edit.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            店舗情報更新
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.shops.update', $shop->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="-m-2">
                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="name" class="leading-7 text-sm text-gray-600">店名 ※必須</label>
                                    <input type="text" id="name" name="name"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                        value="{{ old('name', $shop->name) }}" required />
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="information" class="leading-7 text-sm text-gray-600">店舗情報 ※必須</label>
                                    <textarea id="information" name="information" rows="10"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                        required>{{ old('information', $shop->information) }}</textarea>
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="w-32">
                                    // 編集
                                    <x-shop-thumbnail :filename="$shop->filename" />
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="image" class="leading-7 text-sm text-gray-600">画像</label>
                                    <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/jpg"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out" />
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative flex justify-around">
                                    <div><input type="radio" name="is_selling" value="1" class="mr-2"
                                            @if ($shop->is_selling === 1) { checked } @endif>販売中</div>
                                    <div><input type="radio" name="is_selling" value="0" class="mr-2"
                                            @if ($shop->is_selling === 0) { checked } @endif>停止中</div>
                                </div>
                            </div>

                            <div class="p-2 w-full flex justify-around mt-4">
                                <button type="button" onclick="location.href='{{ route('owner.shops.index') }}'"
                                    class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-gray-400 rounded text-lg">戻る</button>
                                <button type="submit"
                                    class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">更新する</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

## 80 Image 雛形作成

### Image のモデル、マイグレーション

php artisan make:model Image -m <br>

モデル<br>

```php:Image.php
$fillable = ['owner_id', 'filename'];
```

マイグレーション<br>

```php:create_images_table.php
$table
  ->foreignId('owner_id')
  ->constrained()
  ->onUpdate('cascade')
  ->onDelete('cascade');
$table->string('filename');
$table->string('title')->nullable();
```

### Image のコントローラ

php artisan make:controller Owner/ImageController --resource <br>

ルート<br>

```php:owner.php
Route::resource('images', ImageController::class)
  ->middleware('auth:owners')
  ->except('show');
```

### ハンズオン

- `$ php artisan make:model Image -m`を実行<br>

* `$ php artisan make:controller Owner/ImageController --resource`を実行<br>

- `app/Models/Image.php`を編集<br>

```php:Image.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
  use HasFactory;

  protected $fillable = ['owner_id', 'filename'];
}
```

- `database/migrations/create_images_table.php`を編集<br>

```php:create_images_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('images', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('owner_id')
        ->constrained()
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->string('filename');
      $table->string('title')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('images');
  }
}
```

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
// 追加
use App\Http\Controllers\Owner\ImageController;
use App\Http\Controllers\Owner\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('owner.welcome');
});

Route::prefix('shops')
  ->middleware('auth:owners')
  ->group(function () {
    Route::get('index', [ShopController::class, 'index'])->name('shops.index');
    Route::get('edit/{shop}', [ShopController::class, 'edit'])->name(
      'shops.edit'
    );
    Route::post('update/{shop}', [ShopController::class, 'update'])->name(
      'shops.update'
    );
  });

// 追加
Route::resource('images', ImageController::class)
  ->middleware('auth:owners')
  ->except('show');

Route::get('/dashboard', function () {
  return view('owner.dashboard');
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

- `$ php artisan migrate`を実行<br>
