# セクション 08: ユーザー側 その 2

## 141 商品一覧 ローカルスコープ

### 商品一覧を表示する条件

表示する条件<br>
Shop・・is_selling = true<br>
Product ・・is_selling = true<br>
Stock の合計・・1 以上<br>

### 商品一覧クエリ 現状 1

slect 内で sum を使うためクエリビルダの`DB::raw`で対応<br>

```php:SampleController.php
$stocks = DB::table('t_stocks')
  ->select('product_id', DB::raw('sum(quantity) as quantity'))
  ->groupBy('product_id')
  ->having('quantity', '>', 1);
```

### 商品一覧クエリ 現状 2

前ページの `$stocks`をサブクエリとして設定<br>
`products`、`shops`、'stocks`をjoin句で紐付けてwhere句で`is_selling`が`true`かの条件指定<br>

```php:SampleController.php
$products = DB::table('products')
  ->joinSub($stocks, 'stock', function ($join) {
    $join->on('products.id', '=', 'stock.product_id');
  })
  ->join('shops', 'products.shop_id', '=', 'shops.id')
  ->where('shops.is_selling', true)
  ->where('products.is_selling', true)
  ->get();
```

### 商品一覧クエリ 現状 3

Eloquent->クエリビルダに変更したため select で指定<br>

```php:SampleController.php
$products = DB::table('products')
  略
  ->join('secondary_categories', 'products.secondary_category_id', '=', 'secondary_categories.id')
  ->join('images as image1', 'products.image1', '=', 'image1.id')
  ->join('images as image2', 'products.image2', '=', 'image2.id')
  ->join('images as image3', 'products.image3', '=', 'image3.id')
  ->join('images as image4', 'products.image4', '=', 'image4.id')
  略(前ページのwhere句)
  ->select('products.id as id', 'products.name as name', 'products.price', 'products.sort_order as sort_order', 'products.information', 'secondary_categories.name as category', 'image1.filename as filename')
  ->get();
```

### 商品一覧 ローカルスコープ

https://readouble.com/laravel/8.x/ja/eloquent.html <br>

何度も使うクエリは一箇所にまとめておきたい<br>
コントローラをできるだけシンプルにしたい<br>

モデル Product<br>

```php:Product.php
public function scopeAvailableItems($query)
{
  $stocks = 略;

  return $query->joinSub(略 〜:
    ※getは書かない
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
use App\Models\SecondaryCategory;
use App\Models\Image;
use App\Models\Stock;
use App\Models\User;
// 追加
use Illuminate\Support\Facades\DB;

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

  public function users()
  {
    return $this->belongsToMany(User::class, 'carts')->withPivot([
      'id',
      'quantity',
    ]);
  }

  // 追加
  public function scopeAvailableItems($query)
  {
    $stocks = DB::table('t_stocks')
      ->select('product_id', DB::raw('sum(quantity) as quantity'))
      ->groupBy('product_id')
      ->having('quantity', '>=', 1);

    return $query
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
      );
  }
}
```

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;

class ItemController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:users');
  }

  // 編集
  public function index()
  {
    $products = Product::availableItems()->get();

    return view('user.index', compact('products'));
  }
  // ここまで

  public function show($id)
  {
    $product = Product::findOrFail($id);
    $quantity = Stock::where('product_id', $product->id)->sum('quantity');

    if ($quantity > 9) {
      $quantity = 9;
    }

    return view('user.show', compact('product', 'quantity'));
  }
}
```

## 142 商品詳細 コンストラクタの修正

- `app/Http/Controller/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;

class ItemController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:users');

    // 編集
    $this->middleware(function ($request, $next) {
      $id = $request->route()->parameter('item');
      if (!is_null($id)) {
        // null判定
        $itemId = Product::availableItems()
          ->where('products.id', $id)
          ->exists();
        if (!$itemId) {
          abort(404); // 404画面表示
        }
      }

      return $next($request);
    });
    // ここまで
  }

  public function index()
  {
    $products = Product::availableItems()->get();

    return view('user.index', compact('products'));
  }

  public function show($id)
  {
    $product = Product::findOrFail($id);
    $quantity = Stock::where('product_id', $product->id)->sum('quantity');

    if ($quantity > 9) {
      $quantity = 9;
    }

    return view('user.show', compact('product', 'quantity'));
  }
}
```

## 143 表示順 その 1

### 表示順

EC サイトには必須といってもいい機能<br>

おすすめ順、高い順、安い順、新しい順、古い順・・・<br>

大規模な EC サイトになるほど上位ひょうじにお金がかかる<br>
(広告枠 1 週間で ○ 万円など)<br>

### 表示順 定数

```php:Common.php
const ORDER_RECOMMEND = '0';
const ORDER_HIGHER = '1';
const ORDER_LOWER = '2';
const ORDER_LATER = '3';
const ORDER_OLDER = '4';

const SORT_ORDER = [
  'recommend' => self::ORDER_RECOMMEND,
  'higerPrice' => self::ORDER_HIGHER,
  'lowerPrice' => self::ORDER_LOWER,
  'later' => self::ORDER_LATER,
  'older' => self::ORDER_OLDER,
];
```

### 表示順 ローカルスコープ

```php:Product.php
public function scopeSortOrder($query, $sortOrder)
{
  if($sortOrder === null || $sortOrder === \Constant::SORT_ORDER['recommend']) {
    return $query->orderBy('sort_order', 'asc');
  }
  if($sortOrder === \Constant::SORT_ORDER['higherPrice']) {
    return $query->orderBy('price', 'desc');
  }
  if($sortOrder === \Constant::SORT_ORDER['lowerPrice']) {
    return $query->orderBy('price', 'asc');
  }
  if($sortOrder === \Constant::SORT_ORDER['later']) {
    return $query->orderBy('products.created_at', 'desc');
  }
  if($sortOrder === \Constant::SORT_ORDER['older']) {
    return $query->orderBy('products.created_at', 'asc');
  }
}
```

### 表示順 コントローラ

`ItemController`<br>

```php:ItemController.php
public function index(Request $request)
{
  $products = Product::avilableItems()
    ->sortOrder($request->sort)
    ->get();
  略
}
```

### 表示順 ビュー その 1

```php:sample.blade.php
<form method="get" action="{{ route('user.items.index') }}">
  <span class="text-sm">表示順</span><br>
  <select id="sort" name="slot">
    <option value="{{ \Constant::SORT_ORDER['recommended'] }}"
      @if(\Request::get('sort') === \Constant::SORT_ORDER['recommended'])
      selected
      @endif
    >おすすめ順
    </option>
    略
  </select>
</form>
```

### 表示順 ビュー その 2

```php:sample.balde.php
<script>
  const select = document.getElementById('sort')
  select.addEventListener('change', function() {
    this.form.submit()
  })
</script>
```

### ハンズオン

- `app/Constants/Common.php`を編集<br>

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

  // 追加
  const ORDER_RECOMMEND = '0';
  const ORDER_HIGHER = '1';
  const ORDER_LOWER = '2';
  const ORDER_LATER = '3';
  const ORDER_OLDER = '4';

  const SORT_ORDER = [
    'recommend' => self::ORDER_RECOMMEND,
    'higerPrice' => self::ORDER_HIGHER,
    'lowerPrice' => self::ORDER_LOWER,
    'later' => self::ORDER_LATER,
    'older' => self::ORDER_OLDER,
  ];
  // ここまで
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
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

  public function users()
  {
    return $this->belongsToMany(User::class, 'carts')->withPivot([
      'id',
      'quantity',
    ]);
  }

  public function scopeAvailableItems($query)
  {
    $stocks = DB::table('t_stocks')
      ->select('product_id', DB::raw('sum(quantity) as quantity'))
      ->groupBy('product_id')
      ->having('quantity', '>=', 1);

    return $query
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
      );
  }

  // 追加
  public function scopeSortOrder($query, $sortOrder)
  {
    if (
      $sortOrder === null ||
      $sortOrder === \Constant::SORT_ORDER['recommend']
    ) {
      return $query->orderBy('sort_order', 'asc');
    }
    if ($sortOrder === \Constant::SORT_ORDER['higherPrice']) {
      return $query->orderBy('price', 'desc');
    }
    if ($sortOrder === \Constant::SORT_ORDER['lowerPrice']) {
      return $query->orderBy('price', 'asc');
    }
    if ($sortOrder === \Constant::SORT_ORDER['later']) {
      return $query->orderBy('products.created_at', 'desc');
    }
    if ($sortOrder === \Constant::SORT_ORDER['older']) {
      return $query->orderBy('products.created_at', 'asc');
    }
  }
  // ここまで
}
```

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ItemController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:users');

    $this->middleware(function ($request, $next) {
      $id = $request->route()->parameter('item');
      if (!is_null($id)) {
        // null判定
        $itemId = Product::availableItems()
          ->where('products.id', $id)
          ->exists();
        if (!$itemId) {
          abort(404); // 404画面表示
        }
      }

      return $next($request);
    });
  }

  public function index(Request $request)
  {
    // 編集
    $products = Product::availableItems()
      ->sortOrder($request->sort)
      ->get();
    // ここまで

    return view('user.index', compact('products'));
  }

  public function show($id)
  {
    $product = Product::findOrFail($id);
    $quantity = Stock::where('product_id', $product->id)->sum('quantity');

    if ($quantity > 9) {
      $quantity = 9;
    }

    return view('user.show', compact('product', 'quantity'));
  }
}
```
