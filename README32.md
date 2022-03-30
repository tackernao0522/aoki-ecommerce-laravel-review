## 88 Category ダミーデータ

### Category シーダー

`php artisan make:seeder CategoriesTableSeeder`<br>

```php:CategoriesTableSeeder.php
DB::table('primary_categories')->insert([
  // 略
]);

DB::table('secondary_categories')->insert([
  // 略
]);
```

DatabaseSeeder.php にも追記<br>

### ハンズオン

- `$ php artisan make:seeder CategoriesTableSeeder`を実行<br>

* `database/seeders/CategoriesTableSeeder.php`を編集<br>

```php:CategoriesTableSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('primary_categories')->insert([
      [
        'name' => 'キッズファッション',
        'sort_order' => 1,
      ],
      [
        'name' => '出産祝い・ギフト',
        'sort_order' => 2,
      ],
      [
        'name' => 'ベビーカー',
        'sort_order' => 3,
      ],
    ]);

    DB::table('secondary_categories')->insert([
      [
        'primary_category_id' => 1,
        'name' => '靴',
        'sort_order' => 1,
      ],
      [
        'primary_category_id' => 1,
        'name' => 'トップス',
        'sort_order' => 2,
      ],
      [
        'primary_category_id' => 1,
        'name' => 'バッグ・ランドセル',
        'sort_order' => 3,
      ],
      [
        'primary_category_id' => 2,
        'name' => 'ギフトセット',
        'sort_order' => 4,
      ],
      [
        'primary_category_id' => 2,
        'name' => 'メモリアル・記念品',
        'sort_order' => 5,
      ],
      [
        'primary_category_id' => 2,
        'name' => 'おむつケーキ',
        'sort_order' => 6,
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
      ImagesTableSeeder::class,
      CategoriesTableSeeder::class,
    ]);
  }
}
```

- `$ php artisan migrate:fresh --seed`を実行<br>

* `$ php artisan tinker`を実行<br>

- `>>> $category = new App\Models\PrimaryCategory;`を実行<br>

```:terminal
=> App\Models\PrimaryCategory {#4589}
>>>
```

- `>>> $category::findOrFail(1)->secondary`を実行<br>

```:terminal
=> Illuminate\Database\Eloquent\Collection {#4603
     all: [
       App\Models\SecondaryCategory {#4606
         id: 1,
         primary_category_id: 1,
         name: "靴",
         sort_order: 1,
         created_at: null,
         updated_at: null,
       },
       App\Models\SecondaryCategory {#4607
         id: 2,
         primary_category_id: 1,
         name: "トップス",
         sort_order: 2,
         created_at: null,
         updated_at: null,
       },
       App\Models\SecondaryCategory {#4608
         id: 3,
         primary_category_id: 1,
         name: "バッグ・ランドセル",
         sort_order: 3,
         created_at: null,
         updated_at: null,
       },
     ],
   }
```

## 89 Product の雛形作成

### Products Table

|   論理    |          物理          |    データ型     | キー |
| :-------: | :--------------------: | :-------------: | :--: |
|    id     |           id           |     bigint      |  UK  |
|  店舗 id  |        shopt_id        |     bigint      |  FK  |
|  商品名   |          name          |     string      |      |
|   情報    |      information       |      text       |      |
|   価格    |         price          | unsignedInteger |      |
| 販売/停止 |       is_selling       |     boolean     |      |
|  ソート   |       sort_order       |     integer     |      |
| カテゴリ  | secondarty_category_id |     bigint      |  FK  |
|  画像 1   |         image1         |     bigint      |  FK  |
|  画像 2   |         image2         |     bigint      |  FK  |
|  画像 3   |         image3         |     bigint      |  FK  |
|  画像 4   |         image4         |     bigint      |  FK  |
| 作成日時  |       created_at       |    timestamp    |      |
| 更新日時  |       updated_at       |    timestamp    |      |

### Product モデル

php artisan make:model Product -m<br>
php artisan make:controller Owner/ProductController --resource<br>

Shop.php・・hasMany(Proudct::class)<br>

Product.php・・belongsTo(Shop::class)<br>
Product.php・・belongsTo(Image::class)<br>
Product.php・・belongsTo(SecondaryCategory::class)<br>

### ハンズオン

- `$ php artisan make:model Product -m`を実行<br>

* `$ php artisan make:controller Owner/ProductController --resource`を実行<br>

- `routes/owner.php`を編集<br>

```php:owner.php
<?php

use App\Http\Controllers\Owner\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Owner\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Owner\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Owner\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Owner\Auth\NewPasswordController;
use App\Http\Controllers\Owner\Auth\PasswordResetLinkController;
use App\Http\Controllers\Owner\Auth\RegisteredUserController;
use App\Http\Controllers\Owner\Auth\VerifyEmailController;
use App\Http\Controllers\Owner\ImageController;
use App\Http\Controllers\Owner\ProductController;
use App\Http\Controllers\Owner\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('owner.welcome');
});

Route::prefix('shops')
  ->middleware('auth:owners')
  ->group(function () {
    Route::get('index', [ShopController::class, 'index'])->name('shops.index');
    Route::get('edit/{shop}', [ShopController::class, 'edit'])->name(
      'shops.edit'
    );
    Route::post('update/{shop}', [ShopController::class, 'update'])->name(
      'shops.update'
    );
  });

Route::resource('images', ImageController::class)
  ->middleware('auth:owners')
  ->except('show');

// 追加
Route::resource('products', ProductController::class)
  ->middleware('auth:owners')
  ->except('show');

Route::get('/dashboard', function () {
  return view('owner.dashboard');
})
  ->middleware(['auth:owners'])
  ->name('dashboard'); // 認証しているかどうか

Route::middleware('guest')->group(function () {
  Route::get('register', [RegisteredUserController::class, 'create'])->name(
    'register'
  );

  Route::post('register', [RegisteredUserController::class, 'store']);

  Route::get('login', [AuthenticatedSessionController::class, 'create'])->name(
    'login'
  );

  Route::post('login', [AuthenticatedSessionController::class, 'store']);

  Route::get('forgot-password', [
    PasswordResetLinkController::class,
    'create',
  ])->name('password.request');

  Route::post('forgot-password', [
    PasswordResetLinkController::class,
    'store',
  ])->name('password.email');

  Route::get('reset-password/{token}', [
    NewPasswordController::class,
    'create',
  ])->name('password.reset');

  Route::post('reset-password', [NewPasswordController::class, 'store'])->name(
    'password.update'
  );
});

Route::middleware('auth:owners')->group(function () {
  Route::get('verify-email', [
    EmailVerificationPromptController::class,
    '__invoke',
  ])->name('verification.notice');

  Route::get('verify-email/{id}/{hash}', [
    VerifyEmailController::class,
    '__invoke',
  ])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

  Route::post('email/verification-notification', [
    EmailVerificationNotificationController::class,
    'store',
  ])
    ->middleware('throttle:6,1')
    ->name('verification.send');

  Route::get('confirm-password', [
    ConfirmablePasswordController::class,
    'show',
  ])->name('password.confirm');

  Route::post('confirm-password', [
    ConfirmablePasswordController::class,
    'store',
  ]);

  Route::post('logout', [
    AuthenticatedSessionController::class,
    'destroy',
  ])->name('logout');
});
```

- `app/Models/Shop.php`を編集<br>

```php:Shop.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use app\Models\Owner;
use app\Models\Product;

class Shop extends Model
{
  use HasFactory;

  protected $fillable = [
    'owner_id',
    'name',
    'information',
    'filename',
    'is_selling',
  ];

  public function owner()
  {
    return $this->belongsTo(Owner::class);
  }

  // 追加
  public function products()
  {
    return $this->hasMany(Product::class);
  }
}
```

- `app/Models/Product.php`を編集<br>

```php:Product.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;

class Product extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function shop()
  {
    return $this->belongsTo(Shop::class);
  }
}
```

### 90 Product マイグレーション・シーダー

### Product マイグレーション

外部キー制約<br>
親を削除するか、親を削除したときに合わせて削除するか<br>
テーブル名(shops 複数形)とカラム名(shop_id 単数形\_id)が一致するか<br>
Null を許容するか<br>

```php:create_products_table.php
$table->foreginId('shop_id'); // cascadeあり
$table->foreginId('secondary_category_id); // cascadeなし
$table->foreginId('image1')->nullable()->constrained('images');
// null許可、カラム名と違うのでテーブル名を指定
```

- `database/migrations/create_products_table.php`を編集<br>

```php:create_products_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('shop_id')
        ->constrained()
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->foreignId('secondary_category_id')->constrained();
      $table
        ->foreignId('image1')
        ->nullable()
        ->constrained('images');
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
    Schema::dropIfExists('products');
  }
}
```

- `$ php artisan make:seeder ProductsTableSeeder`を実行<br>

* `database/seeders/ProductsTableSeeder.php`を編集<br>

```php:ProductsTableSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('products')->insert([
      [
        'shop_id' => 1,
        'secondary_category_id' => 1,
        'image1' => 1,
      ],
      [
        'shop_id' => 1,
        'secondary_category_id' => 2,
        'image1' => 2,
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
      ImagesTableSeeder::class,
      CategoriesTableSeeder::class,
      // 追加
      ProductsTableSeeder::class,
    ]);
  }
}
```

- `php artisan migrate:fresh --seed`を実行<br>
