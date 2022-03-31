## 91 Product リレーション

リレーション設定で第 2 引数に FK、<br>
第 3 引数で親モデル名を設定可能<br>
カラム名と同じメソッドは指定できないので名称変更<br>

`Product.php`<br>

```php:Product.php
public function category()
{
  return $this->belongsTo(SecondaryCategory::class, 'secondary_category_id');
}

public function imageFirst()
{
  return $this->belongsTo(Image::class, 'image1', 'id');
}
```

### ハンズオン

- `app/Models/Product.php`を編集<br>

```php:Product.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
// 追加
use App\Models\SecondaryCategory;
use App\Models\Image;
// ここまで

class Product extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function shop()
  {
    return $this->belongsTo(Shop::class);
  }

  // 追加
  public function category()
  {
    return $this->belongsTo(SecondaryCategory::class, 'secondary_category_id');
  }

  public function imageFirst()
  {
    return $this->belongsTo(Image::class, 'image1', 'id');
  }
  // ここまで
}
```

- `$ php artisan tinker`を実行<br>

* `>>> $product = new App\Models\Product`を実行<br>

- `$product->all()`を実行<br>

```:terminal
=> Illuminate\Database\Eloquent\Collection {#4608
     all: [
       App\Models\Product {#4609
         id: 1,
         shop_id: 1,
         secondary_category_id: 1,
         image1: 1,
         created_at: null,
         updated_at: null,
       },
       App\Models\Product {#4610
         id: 2,
         shop_id: 1,
         secondary_category_id: 2,
         image1: 2,
         created_at: null,
         updated_at: null,
       },
       App\Models\Product {#4611
         id: 3,
         shop_id: 1,
         secondary_category_id: 3,
         image1: 3,
         created_at: null,
         updated_at: null,
       },
       App\Models\Product {#4612
         id: 4,
         shop_id: 1,
         secondary_category_id: 4,
         image1: 3,
         created_at: null,
         updated_at: null,
       },
       App\Models\Product {#4613
         id: 5,
         shop_id: 1,
         secondary_category_id: 5,
         image1: 4,
         created_at: null,
         updated_at: null,
       },
     ],
   }
```

- `>>> $product::findOrFail(1)`を実行<br>

```:terminal
=> App\Models\Product {#4606
     id: 1,
     shop_id: 1,
     secondary_category_id: 1,
     image1: 1,
     created_at: null,
     updated_at: null,
   }
```

- `>>> $product::findOrFail(1)->shop`を実行<br>

```:terminal
=> App\Models\Shop {#4621
     id: 1,
     owner_id: 1,
     name: "お店の名前が入ります。",
     information: "ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。",
     filename: "",
     is_selling: 1,
     created_at: null,
     updated_at: null,
   }
```

- `$product::findOrFail(1)->shop->owner`を実行<br>

```:terminal
=> App\Models\Owner {#4629
     id: 1,
     name: "Kaira",
     email: "takaproject777@gmail.com",
     email_verified_at: null,
     #password: "$2y$10$H6WT0X/RK2bVJPCXAsQixuBDaeY7ztGoVnW0wY0GJkCKdTQSdqKy6",
     #remember_token: null,
     created_at: "2022-03-16 11:11:11",
     updated_at: null,
     deleted_at: null,
   }
```

- `$product::findOrFail(1)->shop->owner->id`を実行<br>

```:terminal
=> 1
```

- `>>> $product::findOrFail(1)->category`を実行<br>

```:terminal
=> App\Models\SecondaryCategory {#4604
     id: 1,
     primary_category_id: 1,
     name: "靴",
     sort_order: 1,
     created_at: null,
     updated_at: null,
   }
```

- `>>> $product::findOrFail(1)->imageFirst`を実行<br>

```:terminal
=> App\Models\Image {#4612
     id: 1,
     owner_id: 1,
     filename: "sample1.jpg",
     title: null,
     created_at: null,
     updated_at: null,
   }
```

- `>>> $product::findOrFail(1)->imageFirst->filename`を実行<br>

```:terminal
=> "sample1.jpg
```

## 92 Product Index

コンストラクタを設定(ImageController などを参考に)<br>

- `ProductController.php`<br>

```php:ProductController.php
Product::findOrFail($id)->shop->owner->id;

$products = Owner::findOrFail(Auth::id())->shop->product; // 後程修正
```

`owner/images/index.blade.php`を参考<br>

`$images`の箇所を`$products`に変更<br>

### ハンズオン

- `app/Http/Controllers/Owner/ProductController.php`を編集<br>

```php:ProductController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
  // 追加
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
  // 編集
  public function index()
  {
    $products = Owner::findOrFail(Auth::id())->shop->products;

    return view('owner.products.index', compact('products'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
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

- `$ mkdir resources/views/owner/products && touch $_/index.blade.php`を実行<br>

* `resources/views/owner/products/index.blade.php`を編集<br>

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
                  <x-flash-message status="session('status')" />
                  <div class="flex justify-end mb-4">
                      <button onclick="location.href='{{ route('owner.products.create') }}'"
                          class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">新規登録する</button>
                  </div>
                  <div class="flex flex-wrap">
                      @foreach ($products as $product)
                          <div class="w-1/4 p-2 md:p-4">
                              <a href="{{ route('owner.products.edit', $product->id) }}">
                                  <div class="border rounded-md p-2 md:p-4">
                                      <x-thumbnail :filename="$product->imageFirst->filename" type="products" />
                                      {{-- <div class="text-gray-700">
                                          {{ $product->name }}
                                      </div> --}}
                                  </div>
                              </a>
                          </div>
                      @endforeach
                  </div>
              </div>
          </div>
      </div>
  </div>
</x-app-layout>
```

- `resources/views/layouts/owner-navigation.blade.php`を編集<br>

```php:owner-navigation.blade.php
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <div class="w-12">
                        <a href="{{ route('owner.dashboard') }}">
                            <x-application-logo class="block h-10 w-auto fill-current text-gray-600" />
                        </a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('owner.shops.index')" :active="request()->routeIs('owner.shops.index')">
                        店舗情報
                    </x-nav-link>
                    <x-nav-link :href="route('owner.images.index')" :active="request()->routeIs('owner.images.index')">
                        画像管理
                    </x-nav-link>
                    // 追加
                    <x-nav-link :href="route('owner.products.index')" :active="request()->routeIs('owner.products.index')">
                        商品管理
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('owner.logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('owner.logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('owner.shops.index')" :active="request()->routeIs('owner.shops.index')">
                店舗情報
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('owner.images.index')" :active="request()->routeIs('owner.images.index')">
                画像管理
            </x-responsive-nav-link>
            // 追加
            <x-responsive-nav-link :href="route('owner.products.index')" :active="request()->routeIs('owner.products.index')">
                商品管理
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Authentication -->
                <form method="POST" action="{{ route('owner.logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('owner.logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
```

## 93 Eager Loading

### Eager(積極的) Loading

https://readouble.com/laravel/9.x/ja/eloquent-relationships.html (Eager ロード)<br>

N + 1 問題の対策<br>
リレーション先のリレーション情報を取得<br>
with メソッド、リレーションをドットで繋ぐ<br>

```php:ProductController.php
$ownerInfo = Owner::with('shop.product.imageFirst)
  ->where('id', Auth::id())->get();

  foreach ($ownerInfo as $owner) {
      // dd($owner->shop->products);
      foreach($owner->shop->products as $product) {
          dd($product->imageFirst->filename);
      }
  }
```

### ハンズオン

- `app/Http/Controllers/Owner/ProductController.php`を編集<br>

```php:ProductController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
  // 編集
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
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
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

- `resources/views/owner/proudcts/index.blade.php`を編集<br>

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
                    <x-flash-message status="session('status')" />
                    <div class="flex justify-end mb-4">
                        <button onclick="location.href='{{ route('owner.products.create') }}'"
                            class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">新規登録する</button>
                    </div>
                    <div class="flex flex-wrap">
                        // 編集
                        @foreach ($ownerInfo as $owner)
                            @foreach ($owner->shop->products as $product)
                                <div class="w-1/4 p-2 md:p-4">
                                    <a href="{{ route('owner.products.edit', $product->id) }}">
                                        <div class="border rounded-md p-2 md:p-4">
                                            <x-thumbnail :filename="$product->imageFirst->filename" type="products" />
                                            <div class="text-gray-700">
                                                {{ $product->name }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @endforeach
                        // ここまで
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```
