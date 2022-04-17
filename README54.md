## 148 検索フォーム カテゴリー

### カテゴリー ビュー側

```php:index.blade.php
<select name="category">
  <option value="0" @if(\Request::get('category') === '0') selected @endif>
    全て
  </option>
  @foreach($categories as $category)
    <optgroup label="{{ $category->name }}">
      @foreach($category->secondary as $secondary)
        <option value="{{ $secondary->id }}" @if(\Request::get('category') == $sedondary->id) selected @endif>
          {{ $secondary->name }}
        </option>
      @endforeach
    </optgroup>
  @endforeach
</select>
```

### カテゴリー ローカルスコープ

モデル側<br>

```php:Product.php
public function scopeSelectCategory($query, $categoryId)
{
  if($categoryId !== '0') {
    return $query->where('products.secondary_category_id', $categoryId);
  } else {
    return;
  }
}
```

コントローラ側<br>

```php:ItemController.php
$products = Product::availableItems()
  ->selectCategory($request->category ?? '0')
  ->sortOrder($request->sort)
  略
```

### ハンズオン

- `resources/views/user/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            商品一覧
        </h2>
        <form method="get" action="{{ route('user.items.index') }}">
            <div class="lg:flex lg:justify-around">
                <div class="lg:flex items-center">
                    <select name="category" class="mb-2 lg:mb-0 lg:mr-2">
                        // 編集
                        <option value="0" @if (\Request::get('category') === '0') selected @endif>全て</option>
                        @foreach ($categories as $category)
                            <optgroup label="{{ $category->name }}">
                                @foreach ($category->secondary as $secondary)
                                    <option value="{{ $secondary->id }}"
                                        @if (\Request::get('category') == $secondary->id) selected @endif>
                                        // ここまで
                                        {{ $secondary->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div class="flex space-x-2 items-center">
                        <div><input name="keyword" class="border border-gray-500 py-2" placeholder="キーワードを入力"></div>
                        <div><button
                                class="ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">検索する</button>
                        </div>
                    </div>
                </div>
                <div class="flex">
                    <div>
                        <span class="text-sm">表示順</span><br>
                        <select name="sort" id="sort" class="mr-4">
                            <option value="{{ \Constant::SORT_ORDER['recommend'] }}"
                                @if (\Request::get('sort') === \Constant::SORT_ORDER['recommend']) selected @endif>おすすめ順</option>
                            <option value="{{ \Constant::SORT_ORDER['higherPrice'] }}"
                                @if (\Request::get('sort') === \Constant::SORT_ORDER['higherPrice']) selected @endif>料金の高い順</option>
                            <option value="{{ \Constant::SORT_ORDER['lowerPrice'] }}"
                                @if (\Request::get('sort') === \Constant::SORT_ORDER['lowerPrice']) selected @endif>料金の安い順</option>
                            <option value="{{ \Constant::SORT_ORDER['later'] }}"
                                @if (\Request::get('sort') === \Constant::SORT_ORDER['later']) selected @endif>新しい順</option>
                            <option value="{{ \Constant::SORT_ORDER['older'] }}"
                                @if (\Request::get('sort') === \Constant::SORT_ORDER['older']) selected @endif>古い順</option>
                        </select>
                    </div>
                    <div>
                        <span class="text-sm">表示件数</span><br>
                        <select name="pagination" id="pagination">
                            <option value="20" @if (\Request::get('pagination') === '20') selected @endif>20件</option>
                            <option value="50" @if (\Request::get('pagination') === '50') selected @endif>50件</option>
                            <option value="100" @if (\Request::get('pagination') === '100') selected @endif>100件</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-wrap">
                        @foreach ($products as $product)
                            <div class="w-1/4 p-2 md:p-4">
                                <a href="{{ route('user.items.show', $product->id) }}">
                                    <div class="border rounded-md p-2 md:p-4">
                                        <x-thumbnail filename="{{ $product->filename ?? '' }}" type="products" />
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
                    {{ $products->appends([
                            'sort' => \Request::get('sort'),
                            'pagination' => \Request::get('pagination'),
                        ])->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        const select = document.getElementById('sort');
        select.addEventListener('change', function() {
            this.form.submit();
        });
        const paginate = document.getElementById('pagination');
        paginate.addEventListener('change', function() {
            this.form.submit()
        })
    </script>
</x-app-layout>
```

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PrimaryCategory;
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
    // dd($request);
    $categories = PrimaryCategory::with('secondary')->get();

    $products = Product::availableItems()
      // 追加
      ->selectCategory($request->category ?? '0')
      // ここまで
      ->sortOrder($request->sort)
      ->paginate($request->pagination ?? '20');

    return view('user.index', compact('products', 'categories'));
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

  // 追加
  public function scopeSelectCategory($query, $categoryId)
  {
    if ($categoryId !== '0') {
      return $query->where('products.secondary_category_id', $categoryId);
    } else {
      return;
    }
  }
}
```
