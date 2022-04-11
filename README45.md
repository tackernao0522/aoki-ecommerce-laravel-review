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
