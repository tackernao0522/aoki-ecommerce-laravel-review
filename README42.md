## 107 Product Destory

コントローラ<br>
`ImageController@destory` をコピーして調整<br>

ビュー<br>
`images/edit.blade.php`をコピーして調整<br>

### ハンズオン

- `app/Http/Controllers/Owner/ProductController.php`を編集<br>

```php:ProductController.php
    // 〜略〜
    public function destroy($id)
    {
        Product::findOrFail($id)->delete();

        return redirect()->route('owner.products.index')->with([
            'message' => '商品をを削除しました。',
            'status' => 'alert'
        ]);
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
                                    <div><input type="radio" name="type" value="{{ \Constant::PRODUCT_LIST['add'] }}"
                                            class="mr-2" checked>追加
                                    </div>
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
                    // 追記
                    <form id="delete_{{ $product->id }}" action="{{ route('owner.products.destroy', $product->id) }}"
                        method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="p-2 w-full flex justify-around mt-32">
                            <a href="#" data-id="{{ $product->id }}" onclick="deletePost(this)" type="button"
                                class="text-white bg-red-400 border-0 py-2 px-4 focus:outline-none hover:bg-red-500 rounded">削除する</a>
                        </div>
                    </form>
                    // ここまで
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

        // 追加
        function deletePost(e) {
            'use strict';
            if (confirm('本当に削除してもいいですか？')) {
                document.getElementById('delete_' + e.dataset.id).submit();
            }
        }
    </script>
</x-app-layout>
```

## 108 Image 削除時の補足

### Image を削除する場合

Product で選択している Image を削除しようとすると外部キーエラー発生<br>

画像を使っているか確認して<br>
対策 1. Product 側で画像の選択を外してとメッセージを出す<br>
対策 2. Product の image1〜image4 を null に変更<br>

今回は対策 2 で対応<br>

削除したい画像を Product で使っているかの確認<br>

```
$imageInProducts = Product::where('image1', $image->id)
  ->orWhere('image2', $image->id)
  ->orWhere('image3', $image->id)
  ->orWhere('image4', $image->id)
  ->get();

// 使っていたらimage1〜image4をチェックして null に変更
if ($imageInProducts) {
  $imageInProducts->each(function($product) use ($image) {
    if($product->image1 === $image->id) {
      $product->image1 = null;
      $product->save();
    }
  })
}
```

- `app/Http/Controllers/Owner/ImageController.php`を編集<br>

```php:ImageController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use App\Models\Image;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:owners');

    $this->middleware(function ($request, $next) {
      $id = $request->route()->parameter('image'); // imageのid取得
      if (!is_null($id)) {
        // null判定
        $imagesOwnerId = Image::findOrFail($id)->owner->id;
        $imageId = (int) $imagesOwnerId; // キャスト 文字列ー>数値に型変換
        // $imageId = Auth::id();
        if ($imageId !== Auth::id()) {
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
    $images = Image::where('owner_id', Auth::id())
      ->orderBy('updated_at', 'DESC')
      ->paginate(20);

    return view('owner.images.index', compact('images'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('owner.images.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(UploadImageRequest $request)
  {
    $imageFiles = $request->file('files');

    if (!is_null($imageFiles)) {
      foreach ($imageFiles as $imageFile) {
        $fileNameToStore = ImageService::upload($imageFile, 'products');
        Image::create([
          'owner_id' => Auth::id(),
          'filename' => $fileNameToStore,
        ]);
      }
    }

    return redirect()
      ->route('owner.images.index')
      ->with([
        'message' => '画像を登録しました。',
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
    $image = Image::findOrFail($id);

    return view('owner.images.edit', compact('image'));
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
    $request->validate([
      'title' => 'string|max:50',
    ]);

    $image = Image::findOrFail($id);
    $image->title = $request->title;
    $image->save();

    return redirect()
      ->route('owner.images.index')
      ->with([
        'message' => '画像登録を実施しました。',
        'status' => 'info',
      ]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $image = Image::findOrFail($id);

    // 追加
    $imageInProducts = Product::where('image1', $image->id)
      ->orWhere('image2', $image->id)
      ->orWhere('image3', $image->id)
      ->orWhere('image4', $image->id)
      ->get();

    if ($imageInProducts) {
      $imageInProducts->each(function ($product) use ($image) {
        if ($product->image1 === $image->id) {
          $product->image1 = null;
          $product->save();
        } elseif ($product->image2 === $image->id) {
          $product->image2 = null;
          $product->save();
        } elseif ($product->image3 === $image->id) {
          $product->image3 = null;
          $product->save();
        } elseif ($product->image4 === $image->id) {
          $product->image4 = null;
          $product->save();
        }
      });
    }
    // ここまで

    $filePath = 'public/products/' . $image->filename;

    if (Storage::exists($filePath)) {
      Storage::delete($filePath);
    }

    Image::findOrFail($id)->delete();

    return redirect()
      ->route('owner.images.index')
      ->with([
        'message' => '画像を削除しました。',
        'status' => 'alert',
      ]);
  }
}
```
