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

* `app/Models/Stock.php`を編集<br>

```php:Stock.php
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
