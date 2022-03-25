## 76 フォームリクエスト(カスタムリクエスト)

### フォーム(カスタム)リクエスト 1

`php artisan make:request UploadImageRequest`<br>

`App\Http\Requests\UploadImageRequest.php`が生成<br>

```php:UploadImageRequest.php
public function authorrize()
{
  return true;
}

public function rules()
{
  return [
    'image' => 'image|mimes:jpg,jpeg,png|max:2048',
  ];
}
```

### フォーム(カスタム)リクエスト 2

```php:UploadImageRequest.php
public function messages()
{
  return [
    'image' => '指定されたファイルが画像ではありません。',
    'mimes' => '指定された拡張子（jpg/jpeg/png）ではありません。',
    'max' => 'ファイルサイズは2MB以内にしてください。',
  ];
}
```

### ハンズオン

- `$ php artisan make:request UploadImageRequest`を実行<br>

* `app/Http/Request/UploadImageRequest.php`を編集<br>

```php:UploadImageRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'image' => 'image|mimes:jpg,jpeg,png|max:2048',
    ];
  }

  public function messages()
  {
    return [
      'image' => '指定されたファイルが画像ではありません。',
      'mimes' => '指定された拡張子（jpg/jpeg/png）ではありません。',
      'max' => 'ファイルサイズは2MB以内にしてください。',
    ];
  }
}
```

- `app/Http/Controllers/Owner/ShopController.php`を編集<br>

```php:ShopController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
//追加
use App\Http\Requests\UploadImageRequest;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use InterventionImage;

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
    // phpinfo();
    // $ownerId = Auth::id();
    $shops = Shop::where('owner_id', Auth::id())->get();

    return view('owner.shops.index', compact('shops'));
  }

  public function edit($id)
  {
    // dd(Shop::findOrFail($id));
    $shop = Shop::findOrFail($id);

    return view('owner.shops.edit', compact('shop'));
  }

  // 編集
  public function update(UploadImageRequest $request, $id)
  {
    Shop::findOrFail($id);

    $imageFile = $request->image;

    // もし空でなかったら及びアップロードできたら
    if (!is_null($imageFile) && $imageFile->isValid()) {
      // Storage::putFile('public/shops', $imageFile); // リサイズなしの場合
      $fileName = uniqid(rand() . '_');
      $extension = $imageFile->extension();
      $fileNameToStore = $fileName . '.' . $extension;
      $resizedImage = InterventionImage::make($imageFile)
        ->resize(1920, 1080)
        ->encode();
      // dd($imageFile, $resizedImage);

      Storage::put('public/shops/' . $fileNameToStore, $resizedImage);
    }

    return redirect()->route('owner.shops.index');
  }
}
```

- `resources/views/owner/shops/edit.blade.php`を編集<br>

```php:edit.blade.php
<x-app-layout>
    <x-slot name="header">
        // 編集
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            店舗画像更新
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.shops.update', $shop->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="-m-2">
                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="image" class="leading-7 text-sm text-gray-600">画像</label>
                                    <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/jpg"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out" />
                                </div>
                            </div>
                            <div class="p-2 w-full flex justify-around mt-4">
                                <button type="button" onclick="location.href='{{ route('owner.shops.index') }}'"
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
</x-app-layout>
```

## 77 サービスへの切り離し

重複を防ぎ、ファットコントローラを防ぐため<br>

`app/Services/ImageService.php`ファイルを作成<br>

```php:ImageService.php
<?php
namespace App\Services;

use InterventionImage;
use Illuminate\Support\Facades\Storage;

class ImageService
{
  public static function upload($imageFile, $folderName)
  {
    // 省略
    Storage::put(
      'public/' . $folderName . '/' . $fileNameToStore,
      $resizedImage
    );

    return $fileNameToStore;
  }
}
```

### ハンズオン

- `$ mkdir app/Services && touch $_/ImageService.php`を実行<br>

* `app/Services/ImageService.php`を編集<br>

```php:ImageService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InterventionImage;

class ImageService
{
  public static function upload($imageFile, $folderName)
  {
    $fileName = uniqid(rand() . '_');
    $extension = $imageFile->extension();
    $fileNameToStore = $fileName . '.' . $extension;
    $resizedImage = InterventionImage::make($imageFile)
      ->resize(1920, 1080)
      ->encode();

    Storage::put(
      'public/' . $folderName . '/' . $fileNameToStore,
      $resizedImage
    );

    return $fileNameToStore;
  }
}
```

- `app/Http/Controllers/Owner/ShopController.php`を編集<br>

```php:ShopController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use App\Models\Shop;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use InterventionImage;

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
    // phpinfo();
    // $ownerId = Auth::id();
    $shops = Shop::where('owner_id', Auth::id())->get();

    return view('owner.shops.index', compact('shops'));
  }

  public function edit($id)
  {
    // dd(Shop::findOrFail($id));
    $shop = Shop::findOrFail($id);

    return view('owner.shops.edit', compact('shop'));
  }

  // 編集
  public function update(UploadImageRequest $request, $id)
  {
    Shop::findOrFail($id);

    $imageFile = $request->image;

    // もし空でなかったら及びアップロードできたら
    if (!is_null($imageFile) && $imageFile->isValid()) {
      $fileNameToStore = ImageService::upload($imageFile, 'shops');
    }

    return redirect()->route('owner.shops.index');
  }
}
```
