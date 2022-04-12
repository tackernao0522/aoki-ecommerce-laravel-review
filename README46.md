## 119 商品の詳細 View 調整

- `resources/views/user/show.blade.php`を編集<br>

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
                    <div class="md:flex md:justify-around">
                        <div class="md:w-1/2">
                            <x-thumbnail filename="{{ $product->imageFirst->filename ?? '' }}" type="products" />
                        </div>
                        <div class="md:w-1/2 ml-4">
                            <h2 class="mb-4 text-sm title-font text-gray-500 tracking-widest">{{ $product->category->name }}
                            </h2>
                            <h1 class="text-gray-900 text-3xl title-font font-medium mb-4">{{ $product->name }}</h1>
                            <p class="mb-4 leading-relaxed">{{ $product->information }}</p>
                            <div class="flex justify-around items-center">
                              <div>
                                <span class="title-font font-medium text-2xl text-gray-900">{{ number_format($product->price) }}<span class="text-sm text-gray-700">円(税込)</span></span>
                              </div>
                                <div class="flex items-center ml-auto">
                                    <span class="mr-3">数量</span>
                                    <div class="relative">
                                        <select
                                            class="rounded border appearance-none border-gray-300 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-base pl-3 pr-10">
                                            <option>SM</option>
                                            <option>M</option>
                                            <option>L</option>
                                            <option>XL</option>
                                        </select>
                                    </div>
                                </div>
                                <button
                                    class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">カートに入れる</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

## 121 Swiper(カルーセル) その 1

### Swiper.js

多機能・レスポンシブ対応・VanillaJS<br>
https://swiperjs.com/

`npm install swiper@6.7.0`<br>

JS 記入場所<br>

`resources/js/swiper.js`<br>

Laravel Mix に追記 `webpack.mix.js`<br>

```js:webpack.mix.js
mix
  .js('resources/js/app.js', 'public/js')
  .js('resources/js/swipier.js', 'public/js')
// 元ファイル、出力先(ファイル名は同じ)
```

CSS も調整<br>
`resouces/css/swiper.css`<br>

`app.css`で`@import`<br>

CSS・JS 調整後は `npm run dev`でコンパイル<br>

読み込む方法<br>

```
<script src="{{ mix('js/swiper.js') }}"></script>
```

https://swiperjs.com/get-started<br>

### ハンズオン

- `$ touch resources/js/swiper.js`を実行<br>

* `resources/js/swiper.js`を編集<br>

```js:swiper.js
// import Swiper JS
import Swiper from 'swiper';
// import Swiper styles
import 'swiper/css';

// core version + navigation, pagination modules:
import Swiper, { Navigation, Pagination } from 'swiper';
// import Swiper and modules styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

// init Swiper:
const swiper = new Swiper('.swiper', {
  // configure Swiper to use modules
  modules: [Navigation, Pagination],
  ...
  });

const swiper = new Swiper(...);
```

- `$ touch resources/css/swiper.css`を実行<br>

* `resources/css/swiper.css`を編集<br>

```css:swiper.css
.swiper-container {
  width: 600px;
  height: 300px;
}
```

- `resources/css/app.css`を編集<br>

```css:app.css
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';
@import 'micromodal';
/* 追記 */
@import 'swiper';
```

- `resources/js/swiper.js`を編集<br>

```js:swiper.js
// import Swiper JS
import Swiper from 'swiper'
// import Swiper styles
import 'swiper/swiper-bundle.css'

// core version + navigation, pagination modules:
import SwiperCore, { Navigation, Pagination } from 'swiper/core'

// configure Swiper to use modules
SwiperCore.use([Navigation, Pagination])

// init Swiper:
const swiper = new Swiper('.swiper-container', {
  // Optional parameters
  // direction: 'vertical',
  loop: true,

  // If we need pagination
  pagination: {
    el: '.swiper-pagination',
  },

  // Navigation arrows
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },

  // And if we need scrollbar
  scrollbar: {
    el: '.swiper-scrollbar',
  },
})
```

- `webpack.mix.js`を編集<br>

```js:webpack.mix.js
const mix = require('laravel-mix')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
  .js('resources/js/app.js', 'public/js')
  .js('resources/js/swiper.js', 'public/js')
  .postCss('resources/css/app.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss'),
    require('autoprefixer'),
  ])
```

- `$ npm run watch`を実行<br>

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
                    <div class="md:flex md:justify-around">
                        <div class="md:w-1/2">
                            // 編集
                            <div class="swiper-container">
                                <!-- Additional required wrapper -->
                                <div class="swiper-wrapper">
                                    <!-- Slides -->
                                    <div class="swiper-slide"><img src="{{ asset('storage/products/sample1.jpg') }}"
                                            alt=""></div>
                                    <div class="swiper-slide">Slide 2</div>
                                    <div class="swiper-slide">Slide 3</div>
                                    ...
                                </div>
                                <!-- If we need pagination -->
                                <div class="swiper-pagination"></div>

                                <!-- If we need navigation buttons -->
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>

                                <!-- If we need scrollbar -->
                                <div class="swiper-scrollbar"></div>
                            </div>
                            // ここまで
                        </div>
                        <div class="md:w-1/2 ml-4">
                            <h2 class="mb-4 text-sm title-font text-gray-500 tracking-widest">
                                {{ $product->category->name }}
                            </h2>
                            <h1 class="text-gray-900 text-3xl title-font font-medium mb-4">{{ $product->name }}</h1>
                            <p class="mb-4 leading-relaxed">{{ $product->information }}</p>
                            <div class="flex justify-around items-center">
                                <div>
                                    <span
                                        class="title-font font-medium text-2xl text-gray-900">{{ number_format($product->price) }}<span
                                            class="text-sm text-gray-700">円(税込)</span></span>
                                </div>
                                <div class="flex items-center ml-auto">
                                    <span class="mr-3">数量</span>
                                    <div class="relative">
                                        <select
                                            class="rounded border appearance-none border-gray-300 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-base pl-3 pr-10">
                                            <option>SM</option>
                                            <option>M</option>
                                            <option>L</option>
                                            <option>XL</option>
                                        </select>
                                    </div>
                                </div>
                                <button
                                    class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">カートに入れる</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    // 追加
    <script src="{{ mix('js/swiper.js') }}"></script>
</x-app-layout>
```

- `resources/css/swiper.css`を編集<br>

```css:swiper.css
.swiper-container {
  /**
  width: 600px;
  **/
  height: 300px;
}
```

## 121 Swiper.js(カルーセル) その 2

### Swiper.js その 2

読み込む方法<br>

```
<script src="{{ mix('js/swiper.js') }}"></script>
```

`app.js`は全ページで読み込まれる<br>
`swiper.js`として個別で読み込む事で`app.js`を軽くしつつ他ページ表示も遅くならない<br>

### Swiper.js その 3

```
<div class="swiper-slide">
  @if($product->imageFirst->filename !== null)
    <img src="{{ asset('storage/products/' . $product->imageFirst->filename) }}">
  @else
    <img src="">
  @endif
</div>
```

### ハンズオン

- `resources/views/user/show.blade.php`を編集<br>

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
                    <div class="md:flex md:justify-around">
                        <div class="md:w-1/2">
                            <div class="swiper-container">
                                <!-- Additional required wrapper -->
                                <div class="swiper-wrapper">
                                    <!-- Slides -->
                                    // 編集
                                    <div class="swiper-slide">
                                        <img src="{{ $product->imageFirst->filename !== null ? asset('storage/products/' . $product->imageFirst->filename) : '' }}"
                                            alt="">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="{{ $product->imageSecond->filename !== null ? asset('storage/products/' . $product->imageSecond->filename) : '' }}"
                                            alt="">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="{{ $product->imageThird->filename !== null ? asset('storage/products/' . $product->imageThird->filename) : '' }}"
                                            alt="">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="{{ $product->imageFourth->filename !== null ? asset('storage/products/' . $product->imageFourth->filename) : '' }}"
                                            alt="">
                                    </div>
                                    // ここまで
                                </div>
                                <!-- If we need pagination -->
                                <div class="swiper-pagination"></div>

                                <!-- If we need navigation buttons -->
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>

                                <!-- If we need scrollbar -->
                                <div class="swiper-scrollbar"></div>
                            </div>
                        </div>
                        <div class="md:w-1/2 ml-4">
                            <h2 class="mb-4 text-sm title-font text-gray-500 tracking-widest">
                                {{ $product->category->name }}
                            </h2>
                            <h1 class="text-gray-900 text-3xl title-font font-medium mb-4">{{ $product->name }}</h1>
                            <p class="mb-4 leading-relaxed">{{ $product->information }}</p>
                            <div class="flex justify-around items-center">
                                <div>
                                    <span
                                        class="title-font font-medium text-2xl text-gray-900">{{ number_format($product->price) }}<span
                                            class="text-sm text-gray-700">円(税込)</span></span>
                                </div>
                                <div class="flex items-center ml-auto">
                                    <span class="mr-3">数量</span>
                                    <div class="relative">
                                        <select
                                            class="rounded border appearance-none border-gray-300 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-base pl-3 pr-10">
                                            <option>SM</option>
                                            <option>M</option>
                                            <option>L</option>
                                            <option>XL</option>
                                        </select>
                                    </div>
                                </div>
                                <button
                                    class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">カートに入れる</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ mix('js/swiper.js') }}"></script>
</x-app-layout>
```
