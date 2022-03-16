## 48 シーダー（ダミーデータ）

php artisan make:seeder AdminSeeder<br>
php artisan make:seeder OwnerSeeder<br>

`database/seeders`直下に生成<br>

### シーダー(ダミーデータ)手動設定

DB ファサードの`insert`で連想配列で追加<br>
パスワードがあれば Hash ファサードも使う<br>

```
DB::table('owners')->insert([
  [
    'name' => 'test1',
    'email' => 'test1@test.com',
    Hash::make('password123'),
  ],
]);
```

`DatabaseSeeder.php`内で読み込み設定<br>

```
$this->call([
  AdminSeeder::class,
  OwnerSeeder::class,
])
```

### ハンズオン

- `$ php artisan make:seeder OwnersTableSeeder`を実行<br>

```php:OwnersTableSeeder.php
```

- `$ php artisan make:seeder AdminsTableSeeder`を実行<br>

```php:AdminsTableSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminsTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('admins')->insert([
      [
        'name' => 'Takaki',
        'email' => 'takaki55730317@gmail.com',
        'password' => Hash::make('5t5a7k3a'),
        'created_at' => '2022/03/16 11:11:11',
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
    $this->call(OwnersTableSeeder::class);
    $this->call(AdminsTableSeeder::class);
  }
}
```

- `$ php artisan migrate:refresh --seed`を実行<br>
