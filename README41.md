## 106 定数(クラスで対応)

### 定数 Constant

マジックナンバー回避のため 定数クラスを作成<br>

`app/Constants/Common.php`<br>

```php:Common.php
<?php

namespace App\Constants;

class Common
{
  const PRODUCT_ADD = '1';
  const PRODUCT_REDUCE = '2';

  const PRODUCT_LIST = [
    'add' => self::PRODUCT_ADD,
    'reduce' => self::PRODUCT_REDUCE,
  ];
}
```

`config/app.php`の aliasses に追記<br>

```
'Constant' => App\Constants\Common::class,
```

使用時は<br>

```
\Constant::PRODUCT_LIST['add'];
\Constant::PRODUCT_LIST['reduce'];
```

### ハンズオン

- `$ mkdir app/Constants && touch $_/Common.php`を実行<br>

* `app/Constants/Common.php`を編集<br>

```php:Common.php
<?php

namespace App\Constants;

class Common
{
  const PRODUCT_ADD = '1';
  const PRODUCT_REDUCE = '2';

  const PRODUCT_LIST = [
    'add' => self::PRODUCT_ADD,
    'reduce' => self::PRODUCT_REDUCE,
  ];
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
    Intervention\Image\ImageServiceProvider::class,

    /*
     * Application Service Providers...
     */
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
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
    'InterventionImage' => Intervention\Image\Facades\Image::class,
    // 追加
    'Constant' => App\Constants\Common::class,
  ],
];
```

- `app/Http/Controllers/Owner/ProductController.php`を編集<br>

```php:ProductController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Image;
use App\Models\Owner;
use App\Models\PrimaryCategory;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:owners');

    $this->middleware(function ($request, $next) {
      $id = $request->route()->parameter('product'); // productのid取得
      if (!is_null($id)) {
        // null判定
        $productsOwnerId = Product::findOrFail($id)->shop->owner->id;
        $productId = (int) $productsOwnerId; // キャスト 文字列ー>数値に型変換
        // $imageId = Auth::id();
        if ($productId !== Auth::id()) {
          // 同じでなかったら
          abort(404); // 404画面表示
        }
      }

      return $next($request);
    });
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // $products = Owner::findOrFail(Auth::id())->shop->products;

    $ownerInfo = Owner::with('shop.products.imageFirst')
      ->where('id', Auth::id())
      ->get();

    // dd($ownerInfo);

    // foreach ($ownerInfo as $owner) {
    //     // dd($owner->shop->products);
    //     foreach($owner->shop->products as $product) {
    //         dd($product->imageFirst->filename);
    //     }
    // }

    return view('owner.products.index', compact('ownerInfo'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $shops = Shop::where('owner_id', Auth::id())
      ->select('id', 'name')
      ->get();

    $images = Image::where('owner_id', Auth::id())
      ->select('id', 'title', 'filename')
      ->orderBy('updated_at', 'desc')
      ->get();

    $categories = PrimaryCategory::with('secondary')->get();
    // dd($shops, $images, $categories);

    return view(
      'owner.products.create',
      compact('shops', 'images', 'categories')
    );
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(ProductRequest $request)
  {
    // dd($request);
    try {
      DB::transaction(function () use ($request) {
        $product = Product::create([
          'name' => $request->name,
          'information' => $request->information,
          'price' => $request->price,
          'sort_order' => $request->sort_order,
          'shop_id' => $request->shop_id,
          'secondary_category_id' => $request->category,
          'image1' => $request->image1,
          'image2' => $request->image2,
          'image3' => $request->image3,
          'image4' => $request->image4,
          'is_selling' => $request->is_selling,
        ]);

        Stock::create([
          'product_id' => $product->id,
          'type' => 1,
          'quantity' => $request->quantity,
        ]);
      }, 2);
    } catch (Throwable $e) {
      Log::error($e);
      throw $e;
    }

    return redirect()
      ->route('owner.products.index')
      ->with([
        'message' => '商品登録しました。',
        'status' => 'info',
      ]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $product = Product::findOrFail($id);
    $quantity = Stock::where('product_id', $product->id)->sum('quantity');
    $shops = Shop::where('owner_id', Auth::id())
      ->select('id', 'name')
      ->get();

    $images = Image::where('owner_id', Auth::id())
      ->select('id', 'title', 'filename')
      ->orderBy('updated_at', 'desc')
      ->get();

    $categories = PrimaryCategory::with('secondary')->get();

    return view(
      'owner.products.edit',
      compact('product', 'quantity', 'shops', 'images', 'categories')
    );
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(ProductRequest $request, $id)
  {
    $request->validate([
      'current_quantity' => 'required|integer',
    ]);

    $product = Product::findOrFail($id);
    $quantity = Stock::where('product_id', $product->id)->sum('quantity');

    if ($request->current_quantity !== $quantity) {
      $id = $request->route()->parameter('product');

      return redirect()
        ->route('owner.products.edit', ['product' => $id])
        ->with([
          'message' => '在庫数が変更されています。再度確認してください。',
          'status' => 'alert',
        ]);
    } else {
      try {
        DB::transaction(function () use ($request, $product) {
          $product->name = $request->name;
          $product->information = $request->information;
          $product->price = $request->price;
          $product->sort_order = $request->sort_order;
          $product->shop_id = $request->shop_id;
          $product->secondary_category_id = $request->category;
          $product->image1 = $request->image1;
          $product->image2 = $request->image2;
          $product->image3 = $request->image3;
          $product->image4 = $request->image4;
          $product->is_selling = $request->is_selling;
          $product->save();

          // 編集
          if ($request->type === \Constant::PRODUCT_LIST['add']) {
            $newQuantity = $request->quantity;
            // 編集
          } elseif ($request->type === \Constant::PRODUCT_LIST['reduce']) {
            $newQuantity = $request->quantity * -1;
          }
          Stock::create([
            'product_id' => $product->id,
            'type' => $request->type,
            'quantity' => $newQuantity,
          ]);
        }, 2);
      } catch (Throwable $e) {
        Log::error($e);
        throw $e;
      }

      return redirect()
        ->route('owner.products.index')
        ->with([
          'message' => '商品情報を更新しました。',
          'status' => 'info',
        ]);
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }
}
```

- `resources/views/owner/products/edit.blade.php`を編集<br>

```php:edit.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            商品編集
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <x-flash-message status="session('status')" />
                    <form method="post" action="{{ route('owner.products.update', $product->id) }}">
                        @csrf
                        @method('put')
                        <div class="-m-2">
                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="name" class="leading-7 text-sm text-gray-600">商品名 ※必須</label>
                                    <input type="text" id="name" name="name"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                        value="{{ old('name', $product->name) }}" required />
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="information" class="leading-7 text-sm text-gray-600">商品情報 ※必須</label>
                                    <textarea id="information" name="information" rows="10"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                        required>{{ old('information', $product->information) }}</textarea>
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="price" class="leading-7 text-sm text-gray-600">価格 ※必須</label>
                                    <input type="number" id="price" name="price"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                        value="{{ old('price', $product->price) }}" required />
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="sort_order" class="leading-7 text-sm text-gray-600">表示順</label>
                                    <input type="number" id="sort_order" name="sort_order"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                        value="{{ old('sort_order', $product->sort_order) }}" />
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="current_quantity" class="leading-7 text-sm text-gray-600">現在の在庫数</label>
                                    <input type="hidden" id="current_quantity" name="current_quantity"
                                        value="{{ $quantity }}" />
                                    <div
                                        class="w-full bg-gray-100 bg-opacity-50 rounded text-base outline-none text-gray-700 py-1 px-3 leading-8">
                                        {{ $quantity }}</div>
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative flex justify-around">
                                    // value属性を編集
                                    <div><input type="radio" name="type" value="{{ \Constant::PRODUCT_LIST['add'] }}"
                                            class="mr-2" checked>追加
                                    </div>
                                    // value属性を編集
                                    <div><input type="radio" name="type"
                                            value="{{ \Constant::PRODUCT_LIST['reduce'] }}" class="mr-2">削減
                                    </div>
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="quantity" class="leading-7 text-sm text-gray-600">数量 ※必須</label>
                                    <input type="number" id="quantity" name="quantity"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                        value="0" required />
                                    <span class="text-sm">0〜99の範囲で入力してください</span>
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="shop_id" class="leading-7 text-sm text-gray-600">販売する店舗</label>
                                    <select id="shop_id" name="shop_id"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                        @foreach ($shops as $shop)
                                            <option value="{{ $shop->id }}"
                                                {{ old('shop_id', $product->shop_id) == $shop->id ? 'selected' : '' }}>
                                                {{ $shop->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="category" class="leading-7 text-sm text-gray-600">カテゴリー</label>
                                    <select id="category" name="category"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                        @foreach ($categories as $category)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach ($category->secondary as $secondary)
                                                    <option value="{{ $secondary->id }}"
                                                        {{ old('category', $product->secondary_category_id) == $secondary->id ? 'selected' : '' }}>
                                                        {{ $secondary->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <x-select-image :images="$images" currentId="{{ $product->image1 }}"
                                currentImage="{{ $product->imageFirst->filename ?? '' }}" name="image1" />
                            <x-select-image :images="$images" currentId="{{ $product->image2 }}"
                                currentImage="{{ $product->imageSecond->filename ?? '' }}" name="image2" />
                            <x-select-image :images="$images" currentId="{{ $product->image3 }}"
                                currentImage="{{ $product->imageThird->filename ?? '' }}" name="image3" />
                            <x-select-image :images="$images" currentId="{{ $product->image4 }}"
                                currentImage="{{ $product->imageFourth->filename ?? '' }}" name="image4" />

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative flex justify-around">
                                    <div><input type="radio" name="is_selling" value="1" class="mr-2"
                                            @if ($product->is_selling === 1) { checked } @endif>販売中</div>
                                    <div><input type="radio" name="is_selling" value="0" class="mr-2"
                                            @if ($product->is_selling === 0) { checked } @endif>停止中</div>
                                </div>
                            </div>

                            <div class="p-2 w-full flex justify-around mt-4">
                                <button type="button" onclick="location.href='{{ route('owner.products.index') }}'"
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

    <script>
        'use strict'
        const images = document.querySelectorAll('.image') //全てのimageタグを取得
        images.forEach(image => { // 1つずつ繰り返す
            image.addEventListener('click', function(e) { // クリックしたら
                const imageName = e.target.dataset.id.substr(0, 6) //data-idの6文字
                const imageId = e.target.dataset.id.replace(imageName + '_', '') // 6文字カット
                const imageFile = e.target.dataset.file
                const imagePath = e.target.dataset.path
                const modal = e.target.dataset.modal
                // サムネイルと input type=hiddenのvalueに設定
                document.getElementById(imageName + '_thumbnail').src = imagePath + '/' + imageFile
                document.getElementById(imageName + '_hidden').value = imageId
                // MicroModal.close(modal); //モーダルを閉じる  ★コメントアウト
            })
        })
    </script>
</x-app-layout>
```
