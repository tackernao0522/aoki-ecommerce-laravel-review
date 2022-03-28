## 85 Image destroy

`admin/OwnersController@destroy`と<br>
`admin/owners/index.blade.php`を参考に<br>

テーブル情報を削除する前に Storage フォルダ内画像ファイルを削除<br>

```php:ImageController.php
$image = Image::findOrFail($id);
$filePath = 'public/products/' . $image->filename;
if (Storage::exists($filePath)) {
  Storage::delete($filePath);
}
// 削除・リダイレクトは省略
```

### ハンズオン

`app/Http/Controllers/Owner/ImageController.php`を編集<br>

```php:ImageController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use App\Models\Image;
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
  // 編集
  public function destroy($id)
  {
    $image = Image::findOrFail($id);
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

- `resources/views/owner/images/edit.blade.php`を編集<br>

```php:edit.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            画像タイトル編集
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.images.update', $image->id) }}">
                        @csrf
                        @method('put')
                        <div class="-m-2">

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <label for="title" class="leading-7 text-sm text-gray-600">画像タイトル</label>
                                    <input type="text" id="title" name="title"
                                        value="{{ old('title', $image->title) }}"
                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out" />
                                </div>
                            </div>

                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <div class="w-32">
                                        <x-thumbnail :filename="$image->filename" type="products" />
                                    </div>
                                </div>
                            </div>

                            <div class="p-2 w-full flex justify-around mt-4">
                                <button type="button" onclick="location.href='{{ route('owner.images.index') }}'"
                                    class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-gray-400 rounded text-lg">戻る</button>
                                <button type="submit"
                                    class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">更新する</button>
                            </div>
                        </div>
                    </form>
                    // 追加
                    <form id="delete_{{ $image->id }}" action="{{ route('owner.images.destroy', $image->id) }}"
                        method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="p-2 w-full flex justify-around mt-32">
                            <a href="#" data-id="{{ $image->id }}" onclick="deletePost(this)" type="button"
                                class="text-white bg-red-400 border-0 py-2 px-4 focus:outline-none hover:bg-red-500 rounded">削除する</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    // 追加
    <script>
        function deletePost(e) {
            'use strict';
            if (confirm('本当に削除してもいいですか？')) {
                document.getElementById('delete_' + e.dataset.id).submit();
            }
        }
    </script>
</x-app-layout>
```

- `resources/views/components/flash-message.blade.php`を編集<br>

```php:flash-message.blade.php
@props(['status' => 'info'])

@php
if(session('status') === 'info'){ $bgColor = 'bg-blue-300'; }
if(session('status') === 'alert'){ $bgColor = 'bg-red-500'; }
@endphp

@if (session('message'))
    // my-4を追加
    <div class="{{ $bgColor }} w-1/2 mx-auto p-2 my-4 text-white">
        {{ session('message') }}
    </div>
@endif
```

## 86 Image ダミーデータ

`php artisan make:seed ImageSeeder` <br>

画像はリサイズ・リネーム後 `storage/products`フォルダに保存<br>
いくつかのファイル枚を書き換えつつダミーとして登録 sample1.jpg 〜 saple6.jpg<br>

Storage 内ファイルは git にアップすると消えるので`public/images`内に保存しつつ<br>

### ハンズオン

- `$ php artisan make:seeder ImagesTableSeeder`を実行<br>

`database/seeders/ImagesTableSeeder.php`を編集<br>

```php:ImagesTableSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImagesTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('images')->insert([
      [
        'owner_id' => 1,
        'filename' => 'sample1.jpg',
        'title' => null,
      ],
      [
        'owner_id' => 1,
        'filename' => 'sample2.jpg',
        'title' => null,
      ],
      [
        'owner_id' => 1,
        'filename' => 'sample3.jpg',
        'title' => null,
      ],
      [
        'owner_id' => 1,
        'filename' => 'sample4.png',
        'title' => null,
      ],
      [
        'owner_id' => 1,
        'filename' => 'sample5.png',
        'title' => null,
      ],
      [
        'owner_id' => 1,
        'filename' => 'sample6.png',
        'title' => null,
      ],
    ]);
  }
}
```

- `database/seeders/DatabaseSeeder.php`を編集<br>

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    // \App\Models\User::factory(10)->create();
    $this->call([
      OwnersTableSeeder::class,
      AdminsTableSeeder::class,
      ShopsTableSeeder::class,
      // 追加
      ImagesTableSeeder::class,
    ]);
  }
}
```

- `storage/app/public/products`の中のファイルを sample1.jpg〜sample6.jpg くらいまでリネームして、それらをコピーして`public/images`フォルダ内に貼り付ける<br>

* `$ php artisan migrate:fresh --seed`を実行<br>

## 87 Category モデル, マイグレーション

### Category モデル

`php artisan make:model PrimaryCategory -m`<br>
`php artisan make:model SecondaryCategory`<br>

モデル 1 対多のリレーション<br>

```php:PrimaryCategory.php
public function secondary()
{
  return $this->hasMany(SecondaryCategory::class);
}
```

Secondary からは belongsTo<br>

### ハンズオン

- `$ php artisan make:model PrimaryCategory -m`を実行<br>

* `$ php artisan make:model SecondaryCategory`を実行<br>

- `app/Models/PrimaryCategory.php`を編集<br>

```php:PrimaryCategory.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SecondaryCategory;

class PrimaryCategory extends Model
{
  use HasFactory;

  public function secondary()
  {
    return $this->hasMany(SecondaryCategory::class);
  }
}
```

- `app/Models/SecondaryCategory.php`を編集<br>

```php:SecondaryCategory.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PrimaryCategory;

class SecondaryCategory extends Model
{
  use HasFactory;

  public function primary()
  {
    return $this->belongsTo(PrimaryCategory::class);
  }
}
```

- `create_primary_categories_table.php`を`create_categories_table.php`にリネーム<br>

* `database/migrations/create_categories_table.php`を編集<br>

```php:create_categories_table.php`を編集<br>

```php:create_categories_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('primary_categories', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->integer('sort_order');
      $table->timestamps();
    });

    Schema::create('secondary_categories', function (Blueprint $table) {
      $table->id();
      $table->foreignId('primary_category_id')->constrained();
      $table->string('name');
      $table->integer('sort_order');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('secondary_categories');
    Schema::dropIfExists('primary_categories');
  }
}
```

- `$ php artisan migrate`を実行<br>
