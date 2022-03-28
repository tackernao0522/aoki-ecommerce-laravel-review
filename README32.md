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
