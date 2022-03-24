## 71 Shop コントローラミドルウェア

### Shop ルートパラメータの注意

`owner/shops/edit/2/`<br>

edit, update など URL にパラメータを使う場合 URL の数値を直接変更すると<br>

他のオーナーの Shop が見れてしまう・・NG<br>

ログイン済みオーナーの Shop URL でなければ 404 を表示<br>

https://readouble.com/laravel/8.x/ja/controllers.html (コントローラミドルウェア)<br>

### Shop ミドルウェア設定

コンストラクタ内<br>

```php:ShopController.php
$this->middleware(function ($request, $next) {
  $id = $request->route()->parameter('shop'); // shopのid取得
  if (!is_null($id)) {
    // null判定
    $shopsOwnerId = Shop::findOrFail($id)->owner->id;
    $shopId = (int) $shopsOwnerId; // キャスト 文字列ー>数値に型変換
    $ownerId = Auth::id();
    if ($shopId !== $ownerId) {
      // 同じでなかったら
      abort(404); // 404画面表示
    }
  }
});
```

### ハンズオン

- app/Http/Controllers/Owner/ShopController.php`を編集<br>

```php:ShopController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:owners');

    $this->middleware(function ($request, $next) {
      // dd($request->route()->parameter('shop')); // 文字列
      // // dd(Auth::id()); // 数字
      // return $next($request);
      $id = $request->route()->parameter('shop'); // shopのid取得
      if (!is_null($id)) {
        // null判定
        $shopsOwnerId = Shop::findOrFail($id)->owner->id;
        $shopId = (int) $shopsOwnerId; // キャスト 文字列ー>数値に型変換
        $ownerId = Auth::id();
        if ($shopId !== $ownerId) {
          // 同じでなかったら
          abort(404); // 404画面表示
        }
      }

      return $next($request);
    });
  }

  public function index()
  {
    $ownerId = Auth::id();
    $shops = Shop::where('owner_id', $ownerId)->get();

    return view('owner.shops.index', compact('shops'));
  }

  public function edit($id)
  {
    dd(Shop::findOrFail($id));
  }

  public function update(Request $request, $id)
  {
  }
}
```

## 72 カスタムエラーページ

https://readouble.com/laravel/8.x/ja/errors.html (カスタム HTTP エラーページ)<br>

### ハンズオン

- `$ php artisan vendor:publish --tag=laravel-errors`を実行<br>

* `resources/views/errors/404.blade.php`の`@extends('errors::minimal')のminimal`の部分を`illustrated-layout`に変えたりするとデザインが変わる<br>

## 73 Shop Index 画面

- `public/images`ディレクトリに`no_image.jpg`を配置<br>

* `app/Http/Controllers/Owner/ShopController.php`を編集<br>

```php:ShopController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:owners');

    $this->middleware(function ($request, $next) {
      // dd($request->route()->parameter('shop')); // 文字列
      // // dd(Auth::id()); // 数字
      // return $next($request);
      $id = $request->route()->parameter('shop'); // shopのid取得
      if (!is_null($id)) {
        // null判定
        $shopsOwnerId = Shop::findOrFail($id)->owner->id;
        $shopId = (int) $shopsOwnerId; // キャスト 文字列ー>数値に型変換
        $ownerId = Auth::id();
        if ($shopId !== $ownerId) {
          // 同じでなかったら
          abort(404); // 404画面表示
        }
      }

      return $next($request);
    });
  }

  public function index()
  {
    // $ownerId = Auth::id();
    $shops = Shop::where('owner_id', Auth::id())->get(); // 1行で書ける

    return view('owner.shops.index', compact('shops'));
  }

  public function edit($id)
  {
    dd(Shop::findOrFail($id));
  }

  public function update(Request $request, $id)
  {
  }
}
```

- `resources/views/owner/shops/index.blade.php`を編集<br>

```php:index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach ($shops as $shop)
                        <div class="w-1/2 p-4">
                            <a href="{{ route('owner.shops.edit', $shop->id) }}">
                                <div class="border rounded-md p-4">
                                    <div class="mb-4">
                                        @if ($shop->is_selling)
                                            <span class="border p-2 rounded-md bg-blue-400 text-white">販売中</span>
                                        @else
                                            <span class="border p-2 rounded-md bg-red-400 text-white">停止中</span>
                                        @endif
                                    </div>
                                    <div class="text-xl">
                                        {{ $shop->name }}
                                    </div>
                                    <div>
                                        @if (empty($shop->filename))
                                            <img src="{{ asset('images/no_image.jpg') }}">
                                        @else
                                            <img src="{{ asset('storage/shops/' . $shop->filename) }}">
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```
