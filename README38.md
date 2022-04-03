## 101 Product Edit その 1

コントローラ<br>

```php:ProductController.php
$product = Product::findOrFail($id);
$quantity = Stock::where('product_id', $product->id)->sum('quantity');
```

モデル image のリレーション追加<br>

```
public function imageSecond
public function imageThird
public function imageFourth
```

### Product Edit その 2

ビュー側 現在選択している項目に selected をつける<br>

```
<option value="{{ $secondary->id }}"> @if($secondary->id === $product->secondary_category_id) selected @endif>
```

現在選択している項目をコンポーネントに渡す<br>

```
<x-select-image :images="$images" currentId="{{ $product->image1 }}" currentImage="{{ $product->imageFirst->filename ?? '' }}" name="image1">
```

### Product Edit その 3

ビュー/コンポーネント<br>

```
新規登録時は値がないので値の有無を確認
$cImage = $currentImage ?? '';
$cId = $currentId ?? '';

  <img id="{{ $name }}_thumbnail" @if($cImage) src="{{ asset('storage/products/' . $cImage)}}" @else src="" @endif>
  </div>
</div>
<input id="{{ $name }}_hidden" type="hidden" name="{{ $name }}" value="{{ $cId }}">
```

### ハンズオン

- `app/Http/Controllers/Owner/ProductController.php`を編集<br>

```php:ProductController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
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
  public function store(Request $request)
  {
    // dd($request);
    $request->validate([
      'name' => 'required|string|max:50',
      'information' => 'required|string|max:1000',
      'price' => 'required|integer',
      'sort_order' => 'nullable|integer',
      'quantity' => 'required|integer',
      'shop_id' => 'required|exists:shops,id',
      'category' => 'required|exists:secondary_categories,id',
      'image1' => 'nullable|exists:images,id',
      'image2' => 'nullable|exists:images,id',
      'image3' => 'nullable|exists:images,id',
      'image4' => 'nullable|exists:images,id',
      'is_selling' => 'required',
    ]);

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
  // 追加
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
}
```

- `$ touch resources/views/owner/products/edit.blade.php`を実行<br>

* `resources/views/owner/products/edit.blade.php`を編集<br>

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
                    <form method="post" action="{{ route('owner.products.update', $product->id) }}">
                        @csrf
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
                                    <input type="hidden" id="current_quantity" name="current_quantity" value="{{ $quantity }}" />
                                    <div class="w-full bg-gray-100 bg-opacity-50 rounded">{{ $quantity }}</div>
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="quantity" class="leading-7 text-sm text-gray-600">初期在庫 ※必須</label>
                                    <input type="number" id="quantity" name="quantity"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                        value="{{ old('quantity') }}" required />
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

                            <x-select-image :images="$images" name="image1" />
                            <x-select-image :images="$images" name="image2" />
                            <x-select-image :images="$images" name="image3" />
                            <x-select-image :images="$images" name="image4" />

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative flex justify-around">
                                    <div><input type="radio" name="is_selling" value="1" class="mr-2"
                                            checked>販売中</div>
                                    <div><input type="radio" name="is_selling" value="0" class="mr-2">停止中
                                    </div>
                                </div>
                            </div>

                            <div class="p-2 w-full flex justify-around mt-4">
                                <button type="button" onclick="location.href='{{ route('owner.products.index') }}'"
                                    class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-gray-400 rounded text-lg">戻る</button>
                                <button type="submit"
                                    class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">登録する</button>
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

## 102 Product Edit その 2

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
                    <form method="post" action="{{ route('owner.products.update', $product->id) }}">
                        @csrf
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

                            // 編集
                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="current_quantity" class="leading-7 text-sm text-gray-600">現在の在庫数</label>
                                    <input type="hidden" id="current_quantity" name="current_quantity"
                                        value="{{ $quantity }}" />
                                    <div class="w-full bg-gray-100 bg-opacity-50 rounded text-base outline-none text-gray-700 py-1 px-3 leading-8">{{ $quantity }}</div>
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative flex justify-around">
                                    <div><input type="radio" name="type" value="1" class="mr-2" checked>追加
                                    </div>
                                    <div><input type="radio" name="type" value="2" class="mr-2">削減
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
                            // ここまで

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

                            // 編集
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
                            // ここまで

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

- `resources/views/components/select-image.blade.php`を編集<br>

```php:select-image.blade.php
@php
if ($name === 'image1') {
    $modal = 'modal-1';
}
if ($name === 'image2') {
    $modal = 'modal-2';
}
if ($name === 'image3') {
    $modal = 'modal-3';
}
if ($name === 'image4') {
    $modal = 'modal-4';
}
// 追加
$cImage = $currentImage ?? '';
$cId = $currentId ?? '';
@endphp

<div class="modal micromodal-slide" id="{{ $modal }}" aria-hidden="true">
    <div class="modal__overlay z-50" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="{{ $modal }}-title">
            <header class="modal__header">
                <h2 class="text-xl text-gray-700" id="{{ $modal }}-title">
                    ファイルを選択してください
                </h2>
                <button type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </header>
            <main class="modal__content" id="{{ $modal }}-content">
                <div class="flex flex-wrap">
                    @foreach ($images as $image)
                        <div class="w-1/4 p-2 md:p-4">
                            <div class="border rounded-md p-2 md:p-4">
                                <img class="image" data-id="{{ $name }}_{{ $image->id }}"
                                    data-file="{{ $image->filename }}" data-path="{{ asset('storage/products/') }}"
                                    data-micromodal-close src="{{ asset('storage/products/' . $image->filename) }}">
                                <div class="text-gray-700">
                                    {{ $image->title }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </main>
            <footer class="modal__footer">
                <button type="button" class="modal__btn" data-micromodal-close aria-label="閉じる">閉じる</button>
            </footer>
        </div>
    </div>
</div>

<div class="flex justify-around items-center mb-4">
    <a class="py-2 px-4 bg-gray-200" data-micromodal-trigger="{{ $modal }}" href='javascript:;'>ファイルを選択</a>
    <div class="w-1/4">
        // 編集
        <img id="{{ $name }}_thumbnail" src="{{ $cImage ? asset('storage/products/' . $cImage) : '' }}">
    </div>
</div>
// 編集
<input id="{{ $name }}_hidden" type="hidden" name="{{ $name }}" value="{{ $cId }}">
```
