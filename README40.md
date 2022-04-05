## 104 Product Update その 2(楽観的ロック（楽観的排他制御）みたいに)

### Product Update その 2

画面表示後に在庫数が変わっている可能性がある<br>
(Edit〜Update の間でユーザーが購入した場合など)
在庫が同じか確認し違っていたら edit の戻す(楽観的ロックに近い)<br>
`products/edit.blade.php`側にフラッシュメッセージ追加<br>

```
if($request->current_quantity !== $quatntity) {
  $id = $request->route()->parameter('product');

  return redirect->route('owner.products.edit', ['product' => $id]);
} else {
  処理
}
```

### ハンズオン

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

    // 追加
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
    }
    // ここまで
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

- `resources/views/owners/products/edit.blade.php`を編集<br>

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
                    // 追加
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

## 105 Product Update その 3(更新処理)

### Product Update その 3

Product と Stock 同時更新するため<br>
トランザクションをかけておく<br>
`ProductController@store`を参考<br>

```
DB::transaction(function() use ($request, $product) {
  $product->shop_id = $request->shop_id;
  略
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
      // 編集
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

          if ($request->type === '1') {
            $newQuantity = $request->quantity;
          } elseif ($request->type === '2') {
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
      // ここまで
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

- `resources/lang/ja/validation.php`を編集<br>

```php:validation.php
  // 略
  'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        // 追加
        'quantity' => '数量',
    ],
```
