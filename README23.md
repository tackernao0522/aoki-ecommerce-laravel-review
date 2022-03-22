# セクション 06: オーナー側

## 64 オーナーの概要

### オーナーでできる事

オーナープロフィール編集<br>
店舗情報(1 オーナー 1 店舗)<br>
画像登録<br>
商品登録・・(画像、カテゴリ選択、在庫設定)<br>

## 65 Shop 外部キー制約

### 外部キー制約(FK)

参考: https://readouble.com/laravel/8.x/ja/migrations.html (外部キー制約)<br>

php artisan make:model Shop -m <br>

### マイグレーション

```php:create_shops_table.php
$table->foreginId('owner_id')->constrained();
$table->string('name');
$table->text('information');
$table->string('filename');
```

(foreignId メソッドは unsigned が含まれている)<br>

### ダミーデータ Seeder

php artisan make:seeder ShopsTableSeeder<br>

```php:ShopsTableSeeder.php
DB::table('shops')->insert([
  [
    'owner_id' => 1,
    'name' => 'お店の名前が入ります。',
    'information' =>
      'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
    'filename' => '',
  ],
]);
```

外部キー制約がある場合は事前に必要なデータ(Owner)を設定する<br>

```php:DatabaseSeeder.php
$this->call([
  OwnersTableSeeder::class,
  AdminsTableSeeder::class,
  ShopsTableSeeder::class,
]);
```

### ハンズオン

- `$ php artisan make:model Shop -m`を実行<br>

* `database/migrations/create_shops_table.php`を編集<br>

```php:create_shops_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('shops', function (Blueprint $table) {
      $table->id();
      $table->foreignId('owner_id')->constrained();
      $table->string('name');
      $table->text('information');
      $table->string('filename');
      $table->boolean('is_selling');
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
    Schema::dropIfExists('shops');
  }
}
```

- `$ php artisan make:seeder ShopsTableSeeder`を実行<br>

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
        'filename' => '',
        'is_selling' => true,
      ],
      [
        'owner_id' => 2,
        'name' => 'お店の名前が入ります。',
        'information' =>
          'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
        'filename' => '',
        'is_selling' => true,
      ],
    ]);
  }
}
```

- `database/DatabaseSeeder.php`を編集<br>

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
    ]);
  }
}
```

- `$ php artisan migrate:fresh --seed`を実行<br>

## 65 Shop リレーション 1 対 1

https://readouble.com/laravel/8.x/ja/eloquent-relationships.html#one-to-one <br>

### Eloquent リレーション設定

Owner

```php:Owner.php
use App\Models\Shop;

public function shop()
{
  return $this->hasOne(Shop::class);
}
```

Shop

```php:Shop.php
use App\Models\Owner;

public function owner()
{
  return $this->belongeTo(Owner::class);
}
```

### Laravel Tinker で確認

php artisan tinker<br>

`$owner1 = App\Models\Owner::find(1)->shop;`<br>
・・Owner に紐づく Shop 情報を取得<br>

`$shop1 = App\Models\Ahow::find(1)->owner;`<br>
・・Shop に紐づく Owner 情報を取得<br>

### ハンズオン

- `app/Models/Owner.php`を編集<br>

```php:Owner.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Shop;

class Owner extends Authenticatable
{
  use HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = ['name', 'email', 'password'];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ['password', 'remember_token'];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  public function shop()
  {
    return $this->hasOne(Shop::class);
  }
}
```

- `app/Models/Shop.php`を編集<br>

```php:Shop.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use app\Models\Owner;

class Shop extends Model
{
  use HasFactory;

  public function owner()
  {
    return $this->belongsTo(Owner::class);
  }
}
```

- `$ php artisan tinker`を実行<br>

```:terminal
>>> $owner1 = App\Models\Owner::find(1);
=> App\Models\Owner {#4604
     id: 1,
     name: "Kaira",
     email: "takaproject777@gmail.com",
     email_verified_at: null,
     #password: "$2y$10$jIwqyPQ.LrXbpndhOgi5J.Di2b.j0SKHzL.Dn/Y.ZgzuQmvsE29Ea",
     #remember_token: null,
     created_at: "2022-03-16 11:11:11",
     updated_at: null,
     deleted_at: null,
   }
```

```:terminal
>>> $owner1 = App\Models\Owner::find(1)->shop;
=> App\Models\Shop {#4606
     id: 1,
     owner_id: 1,
     name: "お店の名前が入ります。",
     information: "ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。",
     filename: "",
     is_selling: 1,
     created_at: null,
     updated_at: null,
   }
```

```:terminal
>>> $owner1 = App\Models\Owner::find(1)->shop->name;
=> "お店の名前が入ります。"
```

```:terminal
>>> $owner1 = App\Models\Owner::find(1)->shop->is_selling;
=> 1
```

```:terminal
>>> $shop1 = App\Models\Shop::find(1);
=> App\Models\Shop {#4581
     id: 1,
     owner_id: 1,
     name: "お店の名前が入ります。",
     information: "ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。",
     filename: "",
     is_selling: 1,
     created_at: null,
     updated_at: null,
   }
```

```:terminal
=> App\Models\Owner {#4618
     id: 1,
     name: "Kaira",
     email: "takaproject777@gmail.com",
     email_verified_at: null,
     #password: "$2y$10$jIwqyPQ.LrXbpndhOgi5J.Di2b.j0SKHzL.Dn/Y.ZgzuQmvsE29Ea",
     #remember_token: null,
     created_at: "2022-03-16 11:11:11",
     updated_at: null,
     deleted_at: null,
   }
```

```:terminal
>>> $shop1 = App\Models\Shop::find(1)->owner->name;
=> "Kaira"
```

```:terminal
>>> $shop1 = App\Models\Shop::find(1)->owner->email;
=> "takaproject777@gmail.com"
```
