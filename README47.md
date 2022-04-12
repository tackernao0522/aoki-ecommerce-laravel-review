## 123 Shop 情報 その 1

### 店舗情報 その 1

shop のダミーデータに画像が含まれていないので追記<br>
README.md にも追記<br>
(public/sample1.jpg)を`Storage/app/public/shops`フォルダに配置してほしい)<br>

### 店舗情報 その 2

```php:show.blade.php
<div class="border-t border-gray-400 my-8"></div>
<div>この商品を販売しているショップ</div>
<div>{{ $product->shop->name }}</div>
<div>画像は@ifで設定
  {{ $product->shop->filename }}
</div>
<div>ボタン</div>
```

### ハンズオン

- `database/seeders/ShopsTableSeeder.php`を編集<br>

```php:ShopsTableSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopsTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('shops')->insert([
      [
        'owner_id' => 1,
        'name' => 'お店の名前が入ります。',
        'information' =>
          'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
        // 編集
        'filename' => 'sample1.jpg',
        'is_selling' => true,
      ],
      [
        'owner_id' => 2,
        'name' => 'お店の名前が入ります。',
        'information' =>
          'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
        // 編集
        'filename' => 'sample2.jpg',
        'is_selling' => true,
      ],
    ]);
  }
}
```

- `public/images`フォルダの sample1.jpg と sample2.jpg ファイルを`storage/app/public/shops/フォルダにコピーする<br>

* `$ php artisan migrate:fresh --seed`を実行<br>

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
                    // 追加
                    <div class="border-t border-gray-400 my-8"></div>
                    <div class="mb-4 text-center">この商品を販売しているショップ</div>
                    <div class="mb-4 text-center">
                        {{ $product->shop->name }}
                    </div>
                    <div class="mb-4 text-center">
                        <img class="mx-auto w-40 h-40 object-cover rounded-full" src="{{ $product->shop->filename !== null ? asset('storage/shops/' . $product->shop->filename) : '' }}"
                            alt="">
                    </div>
                    <div class="mb-4 text-center">
                        <button type="button"
                            class="text-white bg-gray-400 border-0 py-2 px-6 focus:outline-none hover:bg-gray-500 rounded">ショップの詳細を見る</button>
                    </div>
                    // ここまで
                </div>
            </div>
        </div>
    </div>
    <script src="{{ mix('js/swiper.js') }}"></script>
</x-app-layout>
```

## 124 Shop 情報 その 2 Micromodal.js

https://micromodal.vercel.app/#styling <br>

https://gist.github.com/ghosh/4f94cf497d7090359a5c9f81caf60699 <br>

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
                    <div class="border-t border-gray-400 my-8"></div>
                    <div class="mb-4 text-center">この商品を販売しているショップ</div>
                    <div class="mb-4 text-center">
                        {{ $product->shop->name }}
                    </div>
                    <div class="mb-4 text-center">
                        <img class="mx-auto w-40 h-40 object-cover rounded-full"
                            src="{{ $product->shop->filename !== null ? asset('storage/shops/' . $product->shop->filename) : '' }}"
                            alt="">
                    </div>
                    <div class="mb-4 text-center">
                        // 編集
                        <button data-micromodal-trigger="modal-1" href='javascript:;' type="button"
                            class="text-white bg-gray-400 border-0 py-2 px-6 focus:outline-none hover:bg-gray-500 rounded">ショップの詳細を見る</button>
                        // ここまで
                    </div>
                </div>
            </div>
        </div>
    </div>

    // 追加
    <div class="modal micromodal-slide z-10" id="modal-1" aria-hidden="true">
        <div class="modal__overlay z-10" tabindex="-1" data-micromodal-close>
            <div class="modal__container z-10" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                <header class="modal__header">
                    <h2 class="text-xl text-gray-700" id="modal-1-title">
                        {{ $product->shop->name }}
                    </h2>
                    <button type="button" class="modal__close" aria-label="Close modal"
                        data-micromodal-close></button>
                </header>
                <main class="modal__content" id="modal-1-content">
                    <p>
                        {{ $product->shop->information }}
                    </p>
                </main>
                <footer class="modal__footer">
                    <button type="button" class="modal__btn" data-micromodal-close
                        aria-label="Close this dialog window">閉じる</button>
                </footer>
            </div>
        </div>
    </div>
    // ここまで

    <script src="{{ mix('js/swiper.js') }}"></script>
</x-app-layout>
```
