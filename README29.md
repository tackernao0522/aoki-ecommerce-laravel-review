## 82 Image Create(画像複数アップロード)とバリデーション

https://readouble.com/laravel/8.x/ja/validation.html (配列のバリデーション)<br>

`shops/edit.blade.php`を参考<br>

画像の複数アップロード対応<br>

```php:sample.blade.php
<input type="file" name="files[][image]" multiple 略>
```

フォームリクエストの rules に下記を追加<br>

`app/Http/Requests/UploadImageRequest.php`<br>

```php:uploadImageRequest.php
'files.*.image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
```

### ハンズオン

- `app/Http/Controllers/Owner/ImageController.php`を編集<br>

```php:ImageController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
  public function store(Request $request)
  {
    //
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

- `$ touch resources/views/owner/images/create.blade.php`を実行<br>

```php:create.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            画像登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.images.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="-m-2">

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="image" class="leading-7 text-sm text-gray-600">画像</label>
                                    <input type="file" id="image" name="files[][image]" multiple accept="image/png,image/jpeg,image/jpg"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out" />
                                </div>
                            </div>

                            <div class="p-2 w-full flex justify-around mt-4">
                                <button type="button" onclick="location.href='{{ route('owner.images.index') }}'"
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

- `app/Http/Requests/UploadImageRequest.php`を編集<br>

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
      // 追加
      'files.*.image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
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

- `app/Http/Controllers/Owner/ImageController.php`を編集<br>

```php:ImageController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
  // 編集
  public function store(UploadImageRequest $request)
  {
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

## 83 Image Store

### Image の Store ImageController

ShopController@update を参考<br>

```php:ImageController.php
public function store(UploadImageRequest $request)
{
  $imageFiles = $request->file('files');
  if(!is_null($imageFiles)) {
    foreach($imageFiles as $imageFile) {
      $fileNameToStore = ImageServece::upload($imageFile, 'products');
        Image::create([
          'owner_id' => Auth::id(),
          'filename' => $fileNameToStore,
        ]);
      ]);
    }
  }
}
```

### Image の Store ImageService

```php:ImageService.php
if (is_array($imageFile)) {
  $file = $imageFile['image'];
} else {
  $file = $imageFile;
}

$fileName = uniqid(rand() . '_');
$extension = $file->extension();
$fileNameToStore = $fileName . '.' . $extension;
$resizedImage = InterventionImage::make($file)
  ->resize(1920, 1080)
  ->encode();
Storage::put('public/' . $folderName . '/' . $fileNameToStore, $resizedImage);
```

### ハンズオン

app/Http/Controllers/Owner/ImageController.php`を編集<br>

```php:ImageController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
// 追加
use App\Models\Image;
// 追加
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
  // 編集
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

- `app/Services/ImageService.php`を編集<br>

```php:ImageService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InterventionImage;

class ImageService
{
  public static function upload($imageFile, $folderName)
  {
    // dd($imageFile['image']);
    // 配列かどうかの判定をかける
    if (is_array($imageFile)) {
      $file = $imageFile['image'];
    } else {
      $file = $imageFile;
    }

    $fileName = uniqid(rand() . '_');
    $extension = $file->extension();
    $fileNameToStore = $fileName . '.' . $extension;
    $resizedImage = InterventionImage::make($file)
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

- `resources/views/owner/images/index.blade.php`を編集<br>

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
                    <x-flash-message status="session('status')" />
                    <div class="flex justify-end mb-4">
                        <button onclick="location.href='{{ route('owner.images.create') }}'"
                            class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">新規登録する</button>
                    </div>
                    // 編集
                    <div class="flex flex-wrap">
                        @foreach ($images as $image)
                            <div class="w-1/4 p-4">
                                <a href="{{ route('owner.images.edit', $image->id) }}">
                                    <div class="border rounded-md p-4">
                                        <x-thumbnail :filename="$image->filename" type="products" />
                                        <div class="text-xl">
                                            {{ $image->title }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    {{ $images->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```
