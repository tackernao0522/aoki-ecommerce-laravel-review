## 94 Stock

在庫管理・履歴用のテーブル<br>
マスターテーブル(参照用)、トランザクションテーブル(処理用)

php artisan make:model Stock -m<br>

Product モデルから hasMany<br>

Stock モデル protected \$table = 't_stocks';

マイグレーション
Schema::create('t_stocks',
\$table->tinyInteger('type');
1・・入庫<br>
2・・出庫<br>

### ハンズオン

- `$ php artisn make:model Stock -m`を実行<br>

* `app/Models/Product.php`を編集<br>

```php:Product.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
use App\Models\SecondaryCategory;
use App\Models\Image;
// 追記
use App\Models\Stock;

class Product extends Model
{
  use HasFactory;

  // protected $guarded = [];

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

  // 追記
  public function stocks()
  {
    return $this->hasMany(Stock::class);
  }
}
```

- `app/Models/Stock.php`を編集<br>

```php:Stock.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Stock extends Model
{
  use HasFactory;

  protected $table = 't_stocks';
}
```

- `database/migrations/create_stocks_table.php`を編集<br>

```php:create_stocks_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('t_stocks', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('product_id')
        ->constrained()
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->tinyInteger('type');
      $table->integer('quantity');
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
    Schema::dropIfExists('t_stocks');
  }
}
```

- `$ php artisan make:seeder StocksTableSeeder`を実行<br>

* `database/seeders/DatabaseSeeder.php`を編集<br>

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
      ImagesTableSeeder::class,
      CategoriesTableSeeder::class,
      ProductsTableSeeder::class,
      // 追加
      StocksTableSeeder::class,
    ]);
  }
}
```

- `database/seeders/StocksTableSeeder.php`を編集<br>

```php:StocksTableSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StocksTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('t_stocks')->insert([
      [
        'product_id' => 1,
        'type' => 1,
        'quantity' => 5,
      ],
      [
        'product_id' => 1,
        'type' => 1,
        'quantity' => -2,
      ],
    ]);
  }
}
```

- `$ php artisan miagrate:fresh --seed`を実行<br>

* `$ php artisan tinker`を実行<br>

- `>>> $product = new App\Models\Product`を実行<br>

```
=> App\Models\Product {#4593}
```

`>>> $product::find(1)->stocks`を実行<br>

```
=> Illuminate\Database\Eloquent\Collection {#4607
     all: [
       App\Models\Stock {#4609
         id: 1,
         product_id: 1,
         type: 1,
         quantity: 5,
         created_at: null,
         updated_at: null,
       },
       App\Models\Stock {#4612
         id: 2,
         product_id: 1,
         type: 1,
         quantity: -2,
         created_at: null,
         updated_at: null,
       },
     ],
   }
```

- `>>> $product::find(1)->stocks->sum('quantity')`を実行<br>

```
=> 3
```

## 95 Product Create(カテゴリー)

### Product Create

先に外部キーの項目を設定<br>

`app/Http/Controllers/Owner/ProductController.php`<br>

`ProductController@create`<br>

```php:ProductController.php
$shops = Shop::where('owner_id', Auth::id())
  ->select('id', 'name')
  ->get();

$images = Image::where('owner_id', Auth::id())
  ->select('id', 'title', 'filename')
  ->orderBy('updated_at', 'desc')
  ->get();

$categories = PrimaryCategory::with('secondary')->get();

return view('owner.products.create', compact('shops', '$images', 'categories'));
```

### Category のビュー側

`owner/shops/edit.blade.php`を参考<br>

```php:create.blade.php
<select name="category">
    @foreach ($categories as $category)
        <optgroup label="{{ $category->name }}">
            @foreach ($category->secondary as $secondary)
                <option value="{{ $secondary->id }}" {{ old('category') == $secondary->id ? 'selected' : ''}}>
                    {{ $secondary->name }}
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
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
  // 編集
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

- `$ touch resources/views/owner/products/create.blade.php`を実行<br>

* `resources/views/owner/products/create.blade.php`を編集<br>

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
