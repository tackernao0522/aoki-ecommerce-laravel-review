## 126 Cart(カート) 多対多<br>

複数のユーザーが複数の商品を持てる・・多対多　(User[多]<->Product[多])<br>

中間(pivot)テーブルをはさみ・・1 対多 (User[1]->Cart[多]<-Product[1])<br>

自動で生成するなら product_user(アルファベット順)<br>
今回は Cart というモデルを作成し設定<br>

### カート 多対多

```php:Cart.php
protected $fillable = [
  'user_id',
  'product_id',
  'quantity',
];
```

### カート 多対多 リレーション設定

https://readouble.com/laravel/9.x/ja/eloquent-relationships.html#many-to-many <br>

`User.php`<br>

```php:User.php
public function products()
{
  return $this->belongsToMany(Product::class, 'carts')
    ->withPivot(['id', 'quantity']);
  // 第2引数で中間テーブル名
  // 中間テーブルのカラム取得
  // デフォルトでは関連付けるカラム(user_idとproduct_idのみ取得)
}
```

`Product.php`<br>

```php:Product.php
public function users()
{
  return $this->belongsToMany(User::class, 'carts')
    ->withPivot(['id', 'quantity']);
}
```

### ハンズオン

- `$ php artisan make:model Cart -m`を実行<br>

* `database/migrations/create_carts_table.php`を編集<br>

```php:create_carts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('carts', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('user_id')
        ->constrained()
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table
        ->foreignId('product_id')
        ->constrained()
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->integer('quantity');
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
    Schema::dropIfExists('carts');
  }
}
```

- `$ php artisan migrate`を実行<br>

* `app/Models/Cart.php`を編集<br>

```php:Cart.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
  use HasFactory;

  protected $fillable = ['user_id', 'product_id', 'quantity'];
}
```

- `app/Models/User.php`を編集<br>

```php:User.php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// 追加
use App\Models\Product;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = ['name', 'email', 'password'];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ['password', 'remember_token'];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  // 追加
  public function products()
  {
    return $this->belongsToMany(Product::class, 'carts')->withPivot([
      'id',
      'quantity',
    ]);
  }
}
```

- `app/Models/Product.php`を編集<br>

```php:Product.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
use App\Models\SecondaryCategory;
use App\Models\Image;
use App\Models\Stock;
// 追加
use App\Models\User;

class Product extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function shop()
  {
    return $this->belongsTo(Shop::class);
  }

  public function category()
  {
    return $this->belongsTo(SecondaryCategory::class, 'secondary_category_id');
  }

  public function imageFirst()
  {
    return $this->belongsTo(Image::class, 'image1', 'id');
  }

  public function imageSecond()
  {
    return $this->belongsTo(Image::class, 'image2', 'id');
  }

  public function imageThird()
  {
    return $this->belongsTo(Image::class, 'image3', 'id');
  }

  public function imageFourth()
  {
    return $this->belongsTo(Image::class, 'image4', 'id');
  }

  public function stocks()
  {
    return $this->hasMany(Stock::class);
  }

  // 追加
  public function users()
  {
    return $this->belongsToMany(User::class, 'carts')->withPivot([
      'id',
      'quantity',
    ]);
  }
}
```

## 127 Cart 商品を追加

`routes/web.php`<br>

```php:web.php
Route::prefix('cart')
  ->middleware('auth:users')
  ->group(function () {
    Route::post('add', [CartController::class, 'add'])->name('cart.add');
  });
```

ビュー<br>

`User/show.blade.php` の a タグにリンク記載<br>

```php:show.blade.php
<form method="post" action="{{ route('user.cart.add')}}">  @csrf
 略 (在庫情報)
<button>カートに追加</button>
<input type="hidden" name="product_id" value="{{ $product->id}}"> </form>
```

コントローラ<br>

`php artisan make:controller User/CartController`<br>

### ハンズオン

- `$ php artisan make:controller User/CartController`を実行<br>

* `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController;
// 追加
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('user.welcome');
});

Route::middleware('auth:users')->group(function () {
  Route::get('/', [ItemController::class, 'index'])->name('items.index');
  Route::get('show/{item}', [ItemController::class, 'show'])->name(
    'items.show'
  );
});

// 追加
Route::prefix('cart')
  ->middleware('auth:users')
  ->group(function () {
    Route::post('add', [CartController::class, 'add'])->name('cart.add');
  });

// Route::get('/dashboard', function () {
//     return view('user.dashboard');
// })->middleware(['auth:users'])->name('dashboard'); // 認証しているかどうか

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

- `resources/views/user/show.blade.php`を編集<br>

```php:show.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            商品の詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="md:flex md:justify-around">
                        <div class="md:w-1/2">
                            <!-- Slider main container -->
                            <div class="swiper-container">
                                <!-- Additional required wrapper -->
                                <div class="swiper-wrapper">
                                    <!-- Slides -->
                                    <div class="swiper-slide">
                                        <img src="{{ $product->imageFirst->filename !== null ? asset('storage/products/' . $product->imageFirst->filename) : '' }}"
                                            alt="">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="{{ $product->imageSecond->filename !== null ? asset('storage/products/' . $product->imageSecond->filename) : '' }}"
                                            alt="">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="{{ $product->imageThird->filename !== null ? asset('storage/products/' . $product->imageThird->filename) : '' }}"
                                            alt="">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="{{ $product->imageFourth->filename !== null ? asset('storage/products/' . $product->imageFourth->filename) : '' }}"
                                            alt="">
                                    </div>
                                </div>
                                <!-- If we need pagination -->
                                <div class="swiper-pagination"></div>

                                <!-- If we need navigation buttons -->
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>

                                <!-- If we need scrollbar -->
                                <div class="swiper-scrollbar"></div>
                            </div>
                        </div>
                        <div class="md:w-1/2 ml-4">
                            <h2 class="mb-4 text-sm title-font text-gray-500 tracking-widest">
                                {{ $product->category->name }}</h2>
                            <h1 class="mb-4 text-gray-900 text-3xl title-font font-medium">{{ $product->name }}</h1>
                            <p class="mb-4 leading-relaxed">{{ $product->information }}</p>
                            <div class="flex justify-around items-center">
                                <div>
                                    <span
                                        class="title-font font-medium text-2xl text-gray-900">{{ number_format($product->price) }}<span
                                            class="text-sm text-gray-700">円(税込)</span></span>
                                </div>
                                <form action="{{ route('user.cart.add') }}" method="post">
                                    @csrf
                                    <div class="flex items-center ml-auto">
                                        <span class="mr-3">数量</span>
                                        <div class="relative">
                                            <select name="quantity"
                                                class="rounded border appearance-none border-gray-300 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-base pl-3 pr-10">
                                                @for ($i = 1; $i <= $quantity; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <button
                                        class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">カートに入れる</button>
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-400 my-8"></div>
                    <div class="mb-4 text-center">この商品を販売しているショップ</div>
                    <div class="mb-4 text-center">{{ $product->shop->name }}</div>
                    <div class="mb-4 text-center">
                        <img class="mx-auto w-40 h-40 object-cover rounded-full"
                            src="{{ $product->shop->filename !== null ? asset('storage/shops/' . $product->shop->filename) : '' }}"
                            alt="">
                    </div>
                    <div class="mb-4 text-center">
                        <button data-micromodal-trigger="modal-1" href='javascript:;' type="button"
                            class="text-white bg-gray-400 border-0 py-2 px-6 focus:outline-none hover:bg-gray-500 rounded">ショップの詳細を見る</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal micromodal-slide z-10" id="modal-1" aria-hidden="true">
        <div class="modal__overlay z-10" tabindex="-1" data-micromodal-close>
            <div class="modal__container z-10" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                <header class="modal__header">
                    <h2 class="text-xl text-gray-700" id="modal-1-title">
                        {{ $product->shop->name }}
                    </h2>
                    <button type="button" class="modal__close" aria-label="Close modal"
                        data-micromodal-close></button>
                </header>
                <main class="modal__content" id="modal-1-content">
                    <p>
                        {{ $product->shop->information }}
                    </p>
                </main>
                <footer class="modal__footer">
                    <button type="button" class="modal__btn" data-micromodal-close
                        aria-label="Close this dialog window">閉じる</button>
                </footer>
            </div>
        </div>
    </div>

    <script src="{{ mix('js/swiper.js') }}"></script>
</x-app-layout>
```

- `app/Http/Controllers/User/CartController.php`を編集<br>

```php:CartController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
  public function add(Request $request)
  {
    dd($request);
  }
}
```
