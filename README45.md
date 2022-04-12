## 115 View 側の調整

https://tailblocks.cc/ (ECOMMERCE)<br>

- `resources/views/user/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ホーム
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-wrap">
                        @foreach ($products as $product)
                            <div class="w-1/4 p-2 md:p-4">
                                <a href="{{-- route('owner.products.edit', $product->id) --}}">
                                    <div class="border rounded-md p-2 md:p-4">
                                        <x-thumbnail filename="{{ $product->imageFirst->filename ?? '' }}"
                                            type="products" />
                                        // 編集
                                        <div class="mt-4">
                                            <h3 class="text-gray-500 text-xs tracking-widest title-font mb-1">
                                                {{ $product->category->name }}
                                            </h3>
                                            <h2 class="text-gray-900 title-font text-lg font-medium">
                                                {{ $product->name }}</h2>
                                            <p class="mt-1">{{ number_format($product->price) }}<span
                                                    class="text-sm text-gray-700">円(税込)</span></p>
                                        </div>
                                        // ここまで
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

## 116 商品一覧のクエリ その 1

表示する条件<br>
shop・・is_selling = true<br>
Product・・is_selling = true<br>
Stock の合計・・1 以上<br>

### 商品一覧クエリ その 2

Stock の合計をグループ化->数量が 1 以上<br>

SQL の場合<br>

```
SELECT `product_id`, sum(`quantity`) as `quantity`
FROM `t_stocks`
GROUP BY `product_id`
HAVING `quantity` >= 1
```

検索条件<br>

Where・・groupBy より前に条件指定<br>
Having・・groupBy の後に条件指定<br>

### 商品一覧クエリ その 3

select 内で`sum`を使うためクエリビルダの`DB::raw`で対応<br>

```
$stocks = DB::table('t_stocks')
  ->select('product_id', DB::raw('sum(quantity) as quantity'))
  ->groupBy('product_id')
  ->having('quantity', '>=', 1);
```

### ハンズオン

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
  public function index()
  {
    $stocks = DB::table('t_stocks')
      ->select('product_id', DB::raw('sum(quantity) as quantity'))
      ->groupBy('product_id')
      ->having('quantity', '>=', 1);

    dd($stocks);

    $products = Product::all();

    return view('user.index', compact('products'));
  }
}
```

### 商品一覧クエリ その 4

全ページの `$stocks`をサブクエリとして設定<br>
`products`、`shops`、`stocks`を join 句で紐付けて<br>
where 句で `is_selling`が`true`かの条件指定<br>

https://readouble.com/laravel/9.x/ja/queries.html <br>

```
$products = DB::table('products')
  ->joinSub($stocks, 'stock', function($join) {
    $join->on('products.id', '=', 'stock.product_id');
  })
  ->join('shops', 'products.shop_id', '=', 'shops.id')
  ->where('shops.is_selling', true)
  ->where('products.is_selling', ture)
  ->get();
```

### ハンズオン

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
  public function index()
  {
    $stocks = DB::table('t_stocks')
      ->select('product_id', DB::raw('sum(quantity) as quantity'))
      ->groupBy('product_id')
      ->having('quantity', '>=', 1);

    $products = DB::table('products')
      ->joinSub($stocks, 'stock', function ($join) {
        $join->on('products.id', '=', 'stock.product_id');
      })
      ->join('shops', 'products.shop_id', '=', 'shops.id')
      ->where('shops.is_selling', true)
      ->where('products.is_selling', true)
      ->get();

    // dd($stocks, $products);

    // $products = Product::all();

    return view('user.index', compact('products'));
  }
}
```

## 117 商品一覧のクエリ その 2

### 商品一覧クエリ その 5

Eloquent->クエリビルダに変更したため`select`で指定<br>

```
$products = DB::table('products')
  略
  ->join('secondary_categories', 'products.secondary_category_id', '=', 'secondary_categories.id')
  ->join('images as image1', 'products.image1', '=', 'image1.id')
  ->join('images as image2', 'products.image1', '=', 'image2.id')
  ->join('images as image3', 'products.image1', '=', 'image3.id')
  ->join('images as image4', 'products.image1', '=', 'image4.id')
  略
  ->select('products.id', 'products.name as name', 'products.price', 'products.sort_order as sort_order', 'products.information', 'secondary_categories.name ad category', 'image1.filename as filename')
  ->get();
```

### ハンズオン

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
  public function index()
  {
    $stocks = DB::table('t_stocks')
      ->select('product_id', DB::raw('sum(quantity) as quantity'))
      ->groupBy('product_id')
      ->having('quantity', '>=', 1);

    $products = DB::table('products')
      ->joinSub($stocks, 'stock', function ($join) {
        $join->on('products.id', '=', 'stock.product_id');
      })
      ->join('shops', 'products.shop_id', '=', 'shops.id')
      // 追記
      ->join(
        'secondary_categories',
        'products.secondary_category_id',
        '=',
        'secondary_categories.id'
      )
      ->join('images as image1', 'products.image1', '=', 'image1.id')
      ->join('images as image2', 'products.image1', '=', 'image2.id')
      ->join('images as image3', 'products.image1', '=', 'image3.id')
      ->join('images as image4', 'products.image1', '=', 'image4.id')
      // ここまで
      ->where('shops.is_selling', true)
      ->where('products.is_selling', true)
      // 追記
      ->select(
        'products.id',
        'products.name as name',
        'products.price',
        'products.sort_order as sort_order',
        'products.information',
        'secondary_categories.name as category',
        'image1.filename as filename'
      )
      // ここまで
      ->get();

    // dd($stocks, $products);

    // $products = Product::all();

    return view('user.index', compact('products'));
  }
}
```

- `resources/views/user/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ホーム
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-wrap">
                        @foreach ($products as $product)
                            <div class="w-1/4 p-2 md:p-4">
                                <a href="{{-- route('owner.products.edit', $product->id) --}}">
                                    <div class="border rounded-md p-2 md:p-4">
                                        // 編集 {{ $product->filename　}}に変更
                                        <x-thumbnail filename="{{ $product->filename ?? '' }}"
                                            type="products" />
                                        <div class="mt-4">
                                            <h3 class="text-gray-500 text-xs tracking-widest title-font mb-1">
                                                // 編集
                                                {{ $product->category }}
                                            </h3>
                                            <h2 class="text-gray-900 title-font text-lg font-medium">
                                                {{ $product->name }}</h2>
                                            <p class="mt-1">{{ number_format($product->price) }}<span
                                                    class="text-sm text-gray-700">円(税込)</span></p>
                                        </div>
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

## 118 商品の詳細 準備

ルート<br>

```php:web.php
Route::get('show/{item}', [ItemController:class, 'show'])->name('items.show');
```

コントローラ<br>

```php:ItemController.php
public function show($id)
{
  $product = Product::findOrFail($id);

  return view('user.show', compact('product'));
}
```

ビュー<br>

`user/show.blade.php`<br>

Tailblocks の Ecommerce を参考にレイアウト調整<br>

https://tailblocks.cc/ <br>

### ハンズオン

- `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController;
use App\Http\Controllers\User\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('user.welcome');
});

Route::middleware('auth:users')->group(function () {
  Route::get('/', [ItemController::class, 'index'])->name('items.index');
  // 追加
  Route::get('show/{item}', [ItemController::class, 'show'])->name('items.show');
  );
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

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:users');
  }

  public function index()
  {
    $stocks = DB::table('t_stocks')
      ->select('product_id', DB::raw('sum(quantity) as quantity'))
      ->groupBy('product_id')
      ->having('quantity', '>=', 1);

    $products = DB::table('products')
      ->joinSub($stocks, 'stock', function ($join) {
        $join->on('products.id', '=', 'stock.product_id');
      })
      ->join('shops', 'products.shop_id', '=', 'shops.id')
      ->join(
        'secondary_categories',
        'products.secondary_category_id',
        '=',
        'secondary_categories.id'
      )
      ->join('images as image1', 'products.image1', '=', 'image1.id')
      ->join('images as image2', 'products.image1', '=', 'image2.id')
      ->join('images as image3', 'products.image1', '=', 'image3.id')
      ->join('images as image4', 'products.image1', '=', 'image4.id')
      ->where('shops.is_selling', true)
      ->where('products.is_selling', true)
      ->select(
        'products.id',
        'products.name as name',
        'products.price',
        'products.sort_order as sort_order',
        'products.information',
        'secondary_categories.name as category',
        'image1.filename as filename'
      )
      ->get();

    // dd($stocks, $products);

    // $products = Product::all();

    return view('user.index', compact('products'));
  }

  public function show($id)
  {
    $product = Product::findOrFail($id);

    return view('user.show', compact('product'));
  }
}
```

- `$ touch resources/views/user/show.blade.php`を実行<br>

* `resources/views/user/show.blade.php`を編集<br>

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
                    {{ $product->name }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

- `resources/views/user/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ホーム
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-wrap">
                        @foreach ($products as $product)
                            <div class="w-1/4 p-2 md:p-4">
                                // 編集
                                <a href="{{ route('user.items.show', $product->id) }}">
                                    <div class="border rounded-md p-2 md:p-4">
                                        <x-thumbnail filename="{{ $product->filename ?? '' }}"
                                            type="products" />
                                        <div class="mt-4">
                                            <h3 class="text-gray-500 text-xs tracking-widest title-font mb-1">
                                                {{ $product->category }}
                                            </h3>
                                            <h2 class="text-gray-900 title-font text-lg font-medium">
                                                {{ $product->name }}</h2>
                                            <p class="mt-1">{{ number_format($product->price) }}<span
                                                    class="text-sm text-gray-700">円(税込)</span></p>
                                        </div>
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
