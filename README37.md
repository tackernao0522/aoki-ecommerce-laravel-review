## 100 Product Store

### Product Store その 1

モデル \$fillable を設定(Stock にも)<br>

バリデーション<br>
nullable null 可<br>
外部キーが存在するか<br>
exists:shope,id<br>
exists:secondary_categories,id<br>
exists:images,id<br>

`app/Http/Controllers/Owner/ProductController.php`<br>

```php:ProductController.php
public function store(Request $request)
{
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
        'is_selling' => $request->is_selling
      ]);

      Stock::create([
        'product_id' => $product->id,
        'type' => 1,
        'quantity' => $request->quantity
      ]);
    }, 2);
  } catch (Throwable $e) {
    Log::error($e);
    throw $e;
  }
```

### Product リダイレクト後の index

```php:index.blade.php
image1の画像がない場合にうまく表示されない
<x-thumbnail :filename="$product->imageFirst->filename" />

Null合体演算子でnull判定
<x-thumbnail filename="{{ $product->imageFirst->filename ?? '' }}" />
```

### ハンズオン

- `app/Models/Product.php`を編集<br>

```php:Product.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
use App\Models\SecondaryCategory;
use App\Models\Image;
use App\Models\Stock;

class Product extends Model
{
  use HasFactory;

  // 追記
  protected $guarded = [];

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

  // 追加
  protected $fillable = ['product_id', 'type', 'quantity'];
}
```

- `app/Http/Controller/Owner/ProductController.php`を編集<br>

```php:ProductController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
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
  public function store(Request $request)
  {
    // dd($request);
    $request->validate([
      'name' => 'required|string|max:50',
      'information' => 'required|string|max:1000',
      'price' => 'required|integer',
      'sort_order' => 'nullable|integer',
      'quantity' => 'required|integer',
      'shop_id' => 'required|exists:shops,id',
      'category' => 'required|exists:secondary_categories,id',
      'image1' => 'nullable|exists:images,id',
      'image2' => 'nullable|exists:images,id',
      'image3' => 'nullable|exists:images,id',
      'image4' => 'nullable|exists:images,id',
      'is_selling' => 'required',
    ]);

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

- resources/views/owner/products/index.blade.php`を編集<br>

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
                        <button onclick="location.href='{{ route('owner.products.create') }}'"
                            class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">新規登録する</button>
                    </div>
                    <div class="flex flex-wrap">
                        @foreach ($ownerInfo as $owner)
                            @foreach ($owner->shop->products as $product)
                                <div class="w-1/4 p-2 md:p-4">
                                    <a href="{{ route('owner.products.edit', $product->id) }}">
                                        <div class="border rounded-md p-2 md:p-4">
                                            // 編集
                                            <x-thumbnail filename="{{ $product->imageFirst->filename ?? '' }}"
                                                type="products" />
                                            <div class="text-gray-700">
                                                {{ $product->name }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```
