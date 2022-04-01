## 96 Micromodal.js(画像選択)

シンプル・軽量・VanillaJS<br>
https://micromodal.vercel.app/ <br>

npm instsll micromodal --save <br>

HTML/CSS のサンプル<br>
https://gist.github.com/ghosh/ <br>

### ハンズオン

- `$ npm install micromodal --save`を実行<br>

* `resources/js/bootstrap.js`を編集<br>

```js:bootstrap.js
window._ = require('lodash')

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios')

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// 追加
import MicroModal from 'micromodal' // es6 module

MicroModal.init({
  disableScroll: true,
})
// ここまで

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
```

- `$ touch resources/css/micromodal.css`を実行<br>

* `resources/css/micromodlal.css`を編集<br>

```css:micromodal.css
/**************************\
  Basic Modal Styles
\**************************/

.modal {
  font-family: -apple-system, BlinkMacSystemFont, avenir next, avenir, helvetica
      neue, helvetica, ubuntu, roboto, noto, segoe ui, arial, sans-serif;
}

.modal__overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal__container {
  background-color: #fff;
  padding: 30px;
  max-width: 1200px;
  max-height: 100vh;
  border-radius: 4px;
  overflow-y: auto;
  box-sizing: border-box;
}

.modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal__title {
  margin-top: 0;
  margin-bottom: 0;
  font-weight: 600;
  font-size: 1.25rem;
  line-height: 1.25;
  color: #00449e;
  box-sizing: border-box;
}

.modal__close {
  background: transparent;
  border: 0;
}

.modal__header .modal__close:before {
  content: '\2715';
}

.modal__content {
  margin-top: 2rem;
  margin-bottom: 2rem;
  line-height: 1.5;
  color: rgba(0, 0, 0, 0.8);
}

.modal__btn {
  font-size: 0.875rem;
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  background-color: #e6e6e6;
  color: rgba(0, 0, 0, 0.8);
  border-radius: 0.25rem;
  border-style: none;
  border-width: 0;
  cursor: pointer;
  -webkit-appearance: button;
  text-transform: none;
  overflow: visible;
  line-height: 1.15;
  margin: 0;
  will-change: transform;
  -moz-osx-font-smoothing: grayscale;
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
  -webkit-transform: translateZ(0);
  transform: translateZ(0);
  transition: -webkit-transform 0.25s ease-out;
  transition: transform 0.25s ease-out;
  transition: transform 0.25s ease-out, -webkit-transform 0.25s ease-out;
}

.modal__btn:focus,
.modal__btn:hover {
  -webkit-transform: scale(1.05);
  transform: scale(1.05);
}

.modal__btn-primary {
  background-color: #00449e;
  color: #fff;
}

/**************************\
  Demo Animation Style
\**************************/
@keyframes mmfadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes mmfadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

@keyframes mmslideIn {
  from {
    transform: translateY(15%);
  }
  to {
    transform: translateY(0);
  }
}

@keyframes mmslideOut {
  from {
    transform: translateY(0);
  }
  to {
    transform: translateY(-10%);
  }
}

.micromodal-slide {
  display: none;
}

.micromodal-slide.is-open {
  display: block;
}

.micromodal-slide[aria-hidden='false'] .modal__overlay {
  animation: mmfadeIn 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.micromodal-slide[aria-hidden='false'] .modal__container {
  animation: mmslideIn 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.micromodal-slide[aria-hidden='true'] .modal__overlay {
  animation: mmfadeOut 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.micromodal-slide[aria-hidden='true'] .modal__container {
  animation: mmslideOut 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.micromodal-slide .modal__container,
.micromodal-slide .modal__overlay {
  will-change: transform;
}
```

- `resources/css/app.css`を編集<br>

```css:app.css
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';
/* 追加 */
@import 'micromodal';
```

- `resources/views/owner/products/create.blade.php`を編集<br>

```php:create.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            商品登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.products.store') }}">
                        @csrf
                        <div class="-m-2">
                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <select name="category">
                                        @foreach ($categories as $category)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach ($category->secondary as $secondary)
                                                    <option value="{{ $secondary->id }}"
                                                        {{ old('category') == $secondary->id ? 'selected' : '' }}>
                                                        {{ $secondary->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            // 追加
                            <x-select-image name="image1" />

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
</x-app-layout>
```

- `$ touch resources/views/components/select-image.blade.php`を実行<br>

* `resources/views/components/select-image.blade.php`を編集<br>

```php:select-image.blade.php
<div class="modal micromodal-slide" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
            <header class="modal__header">
                <h2 class="modal__title" id="modal-1-title">
                    Micromodal
                </h2>
                <button type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </header>
            <main class="modal__content" id="modal-1-content">
                <p>
                    Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also,
                    <code>esc</code> to close modal.
                </p>
            </main>
            <footer class="modal__footer">
                <button type="button" class="modal__btn modal__btn-primary">Continue</button>
                <button type="button" class="modal__btn" data-micromodal-close
                    aria-label="Close this dialog window">Close</button>
            </footer>
        </div>
    </div>
</div>

<a data-micromodal-trigger="modal-1" href='javascript:;'>Open Modal Dialog</a>
```

## 97 Micromodal.js その 2

### Micromodal.js HTML 設定

```
@php // image1〜4を変数で設定する
  if($name === 'image1'){
    $modal = 'modal-1';
  }
@endphp
id="modal-1"となっている箇所を $modalの置き換える

閉じる・ファイルを選択するに変更 Continueボタンを削除

images/index.blade.phpの画像表示部分(foreach)をコピー

x-thumbnailをimgタグに変更
```

画像をクリックしたら画像を選択しつつモーダルを閉じる<br>
JS で操作できるよう共通の CSS と個別の id や属性をつける<br>
(data-○○ とつけると、JS で e.target.dataset.○○ で取得できる)<br>
PHP の変数を JS に渡す方法の一つ<br>

```
<img class="image" data-id="{{ $name }}_{{ $image->id }}"
  data-file="{{ $image->filename }}"
  data-path="{{ asset('storage/products/') }}"
  data-modal="{{ $modal }}"
  src="{{ asset('storage/products/' . $image->filename )}}"
>
```

プレビューエリアと input タグ(hidden)<br>

```
<div class="flex justify-around items-center mb-4">
  <a>開くボタン</a>
  <div class="w-1/4">
    <img id="{{ $name }}_thumbnail" src="">
  </div>
</div>
<input id="{{ $name }}_hidden" type="hidden" name="{{ $name }}" value="">
```

### Micromodal.js JS 設定<br>

```js:Micromodal.js
<script>
  'use strict' const images = document.querySelectorAll('.image')
  images.forEach(image =>{' '}
  {image.addEventListener('click', function (e) {
    const imageName = e.target.dataset.id.substr(0, 6)
    const imageId = e.target.dataset.id.replace(imageName + '_', '')
    const imageFile = e.target.dataset.file
    const imagePath = e.target.dataset.path
    const modal = e.target.dataset.modal
    document.getElementById(imageName + '_thumbnail').src =
      imagePath + '/' + imageFile
    document.getElementById(imageName + '_hidden').value = imageId
    MicroModal.close(modal)
  })}
  )
</script>
```

### ハンズオン

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
@endphp

<div class="modal micromodal-slide" id="{{ $modal }}" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
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
                                    data-modal="{{ $modal }}"
                                    src="{{ asset('storage/products/' . $image->filename) }}">
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
    <a data-micromodal-trigger="{{ $modal }}" href='javascript:;'>ファイルを選択</a>
    <div class="w-1/4">
        <img id="{{ $name }}_thumbnail" src="">
    </div>
</div>
<input id="{{ $name }}_hidden" type="hidden" name="{{ $name }}" value="">
```

- `resources/views/owner/products/create.blade.php`を編集<br>

```php:create.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            商品登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.products.store') }}">
                        @csrf
                        <div class="-m-2">
                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <select name="category">
                                        @foreach ($categories as $category)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach ($category->secondary as $secondary)
                                                    <option value="{{ $secondary->id }}"
                                                        {{ old('category') == $secondary->id ? 'selected' : '' }}>
                                                        {{ $secondary->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <x-select-image :images="$images" name="image1" />

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
        const images = document.querySelectorAll('.image')

        images.forEach(image => {
            image.addEventListener('click', function(e) {
                const imageName = e.target.dataset.id.substr(0, 6)
                const imageId = e.target.dataset.id.replace(imageName + '_', '')
                const imageFile = e.target.dataset.file
                const imagePath = e.target.dataset.path
                const modal = e.target.dataset.modal
                document.getElementById(imageName + '_thumbnail').src = imagePath + '/' + imageFile
                document.getElementById(imageName + '_hidden').value = imageId
                MicroModal.close(modal);
            }, )
        })
    </script>
</x-app-layout>
```

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
    // 追加
    dd($request);
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

```:browner
Illuminate\Http\Request {#43 ▼
  #json: null
  #convertedFiles: null
  #userResolver: Closure($guard = null) {#1372 ▶}
  #routeResolver: Closure() {#1382 ▶}
  +attributes: Symfony\Component\HttpFoundation\ParameterBag {#45 ▶}
  +request: Symfony\Component\HttpFoundation\InputBag {#44 ▼
    #parameters: array:3 [▼
      "_token" => "JmvkF4v0UvvWNaDjejOYSNBVAM9PGhmTSphQmG1l"
      "category" => "1"
      "image1" => "4"
    ]
  }
  +query: Symfony\Component\HttpFoundation\InputBag {#51 ▶}
  +server: Symfony\Component\HttpFoundation\ServerBag {#47 ▶}
  +files: Symfony\Component\HttpFoundation\FileBag {#48 ▶}
  +cookies: Symfony\Component\HttpFoundation\InputBag {#46 ▶}
  +headers: Symfony\Component\HttpFoundation\HeaderBag {#49 ▶}
  #content: null
  #languages: null
  #charsets: null
  #encodings: null
  #acceptableContentTypes: null
  #pathInfo: "/owner/products"
  #requestUri: "/owner/products"
  #baseUrl: ""
  #basePath: null
  #method: "POST"
  #format: null
  #session: Illuminate\Session\Store {#1420 ▶}
  #locale: null
  #defaultLocale: "en"
  -preferredFormat: null
  -isHostValid: true
  -isForwardedValid: true
  -isSafeContentPreferred: null
  basePath: ""
  format: "html"
}
```

- `resources/views/owner/products/create.blade.php`を編集<br>

```php:create.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            商品登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.products.store') }}">
                        @csrf
                        <div class="-m-2">
                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <select name="category">
                                        @foreach ($categories as $category)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach ($category->secondary as $secondary)
                                                    <option value="{{ $secondary->id }}"
                                                        {{ old('category') == $secondary->id ? 'selected' : '' }}>
                                                        {{ $secondary->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            // 編集
                            <x-select-image :images="$images" name="image1" />
                            <x-select-image :images="$images" name="image2" />
                            <x-select-image :images="$images" name="image3" />
                            <x-select-image :images="$images" name="image4" />

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
        const images = document.querySelectorAll('.image')

        images.forEach(image => {
            image.addEventListener('click', function(e) {
                const imageName = e.target.dataset.id.substr(0, 6)
                const imageId = e.target.dataset.id.replace(imageName + '_', '')
                const imageFile = e.target.dataset.file
                const imagePath = e.target.dataset.path
                const modal = e.target.dataset.modal
                document.getElementById(imageName + '_thumbnail').src = imagePath + '/' + imageFile
                document.getElementById(imageName + '_hidden').value = imageId
                MicroModal.close(modal);
            }, )
        })
    </script>
</x-app-layout>
```

```:browser
Illuminate\Http\Request {#43 ▼
  #json: null
  #convertedFiles: null
  #userResolver: Closure($guard = null) {#1372 ▶}
  #routeResolver: Closure() {#1382 ▶}
  +attributes: Symfony\Component\HttpFoundation\ParameterBag {#45 ▶}
  +request: Symfony\Component\HttpFoundation\InputBag {#44 ▼
    #parameters: array:6 [▼
      "_token" => "JmvkF4v0UvvWNaDjejOYSNBVAM9PGhmTSphQmG1l"
      "category" => "1"
      "image1" => "1"
      "image2" => "2"
      "image3" => "4"
      "image4" => null // おかしい
    ]
  }
  +query: Symfony\Component\HttpFoundation\InputBag {#51 ▶}
  +server: Symfony\Component\HttpFoundation\ServerBag {#47 ▶}
  +files: Symfony\Component\HttpFoundation\FileBag {#48 ▶}
  +cookies: Symfony\Component\HttpFoundation\InputBag {#46 ▶}
  +headers: Symfony\Component\HttpFoundation\HeaderBag {#49 ▶}
  #content: null
  #languages: null
  #charsets: null
  #encodings: null
  #acceptableContentTypes: null
  #pathInfo: "/owner/products"
  #requestUri: "/owner/products"
  #baseUrl: ""
  #basePath: null
  #method: "POST"
  #format: null
  #session: Illuminate\Session\Store {#1420 ▶}
  #locale: null
  #defaultLocale: "en"
  -preferredFormat: null
  -isHostValid: true
  -isForwardedValid: true
  -isSafeContentPreferred: null
  basePath: ""
  format: "html"
}
```