## 144 表示順 その 2

- `resources/views/user/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        // 編集
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                商品一覧
            </h2>
            <div>
                <form method="get" action="{{ route('user.items.index') }}">
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
                        <div>表示件数</div>
                    </div>
                </form>
            </div>
        </div>
        // ここまで
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
                </div>
            </div>
        </div>
    </div>

    <script>
        const select = document.getElementById('sort');
        select.addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</x-app-layout>
```

## 145 表示件数

### 表示件数 ビュー その 1

20 件、50 件、100 件の 3 択<br>

```php:index.blade.php
<span class="text-sm">表示件数</span><br>
<select id="pagination" name="pagination">
  <option value="20"
    @if(\Requeset::get('pagination') === '20')
    selected
    @endif
  >
    20件
  </option>
  略
</select>
```

### 表示件数 ビュー その 2

pagination で get パラメータの引き継ぎ<br>

```php:index.blade.php
{{ $products->appends([
  'sort' => \Request::get('sort'),
  'pagination' => \Request::get('pagination'),
])->links() }}

<script>
  const paginate = document.getElementById('pagination')
  paginate.addEventListener('change', function() {
    this.form.submit()
  })
</script>
```

### 表示件数 コントローラ

`ItemController`<br>

```php:ItemController.php
$products = Product::availableItems()
  ->sortOrder($request->sort)
  ->paginate($request->pagination);
```

### ハンズオン

- `resources/views/user/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                商品一覧
            </h2>
            <div>
                <form method="get" action="{{ route('user.items.index') }}">
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
                            //  編集
                            <span class="text-sm">表示件数</span><br>
                            <select id="pagination" name="pagination">
                                <option value="20" @if (\Request::get('pagination' === '20')) selected @endif>
                                    20件
                                </option>
                                <option value="50" @if (\Request::get('pagination' === '50')) selected @endif>
                                    50件
                                </option>
                                <option value="100" @if (\Request::get('pagination' === '100')) selected @endif>
                                    100件
                                </option>
                            </select>
                            // ここまで
                        </div>
                    </div>
                </form>
            </div>
        </div>
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
        // 追加
        const paginate = document.getElementById('pagination')
        paginate.addEventListener('change', function() {
            this.form.submit
        })
    </script>
</x-app-layout>
```

- `app/Http/Controllers/User/ItemController.php`を編集<br>

```php:ItemController.php
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                商品一覧
            </h2>
            <div>
                <form method="get" action="{{ route('user.items.index') }}">
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
                            // 編集
                            <span class="text-sm">表示件数</span><br>
                            <select name="pagination" id="pagination">
                                <option value="20" @if (\Request::get('pagination') === '20') selected @endif>20件</option>
                                <option value="50" @if (\Request::get('pagination') === '50') selected @endif>50件</option>
                                <option value="100" @if (\Request::get('pagination') === '100') selected @endif>100件</option>
                            </select>
                            // ここまで
                        </div>
                    </div>
                </form>
            </div>
        </div>
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
                    // 追加
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
        // 追加
        const paginate = document.getElementById('pagination')
        paginate.addEventListener('change', function() {
            this.form.submit()
        })
    </script>
</x-app-layout>
```

## 149 pagination 一部修正

### 表示件数 コントローラ

`ItemController`<br>

```php:ItemController.php
$products = Product::availableItems()
  ->sortOrder($request->sort)
  ->paginate($request->pagination);

  // 修正
  ->paginate($request->pagination ?? '20');
```

### ハンズオン

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
    $products = Product::availableItems()
      ->sortOrder($request->sort)
      // 編集
      ->paginate($request->pagination ?? '20');

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
