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
