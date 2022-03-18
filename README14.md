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

## 49 データを扱う方法の比較

### データを扱う方法 比較表

|                |                       コレクション<br>Collection                        |                    クエリビルダ<br>QueryBuilder                     |                     エロクアント<br>Eloquent(モデル)                     |
| :------------: | :---------------------------------------------------------------------: | :-----------------------------------------------------------------: | :----------------------------------------------------------------------: |
|    データ型    |                      Illuminate\Support\Collection                      |                    Illuminate\Support\Collection                    |        Illuminate\Database\Eloquent\Collection(Collection を継承)        |
|    使用方法    |                      collect();<br>new Collection;                      | use Illuminate\Supports\Facades\DB;<br>DB:table(テーブル名)->get(); |              モデル名::all();<br>モデル名::select()->get();              |
| 関連マニュアル |                              コレクション                               |                    コレクション<br>クエリビルダ                     | コレクション、クエリビルダ、<br>エロクアント、エロクアントのコレクション |
|      特徴      |                               配列を拡張                                |                             SQL に近い                              |                               OR マッパー                                |
|    メリット    |                           多数の専用メソッド                            |                   SQL を知っているとわかりやすい                    |                    簡潔に書ける<br>リレーションが強力                    |
|   デメリット   | 返り値に複数のパターンあり<br>(stdClass, Collection, モデル Collection) |                        コードが長くなりがち                         |                       覚えることが多い<br>やや遅い                       |

- `app/Http/Controllers/Admin/OwnersController.php`を編集<br>

```php:OwnersController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner; // eloquent エロクアント
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // QueryBuilder クエリビルダ

class OwnersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:admin');
  }

  // 編集
  public function index()
  {
    $e_all = Owner::all();
    $q_get = DB::table('owners')
      ->select('name')
      ->get();
    $q_first = DB::table('owners')
      ->select('name')
      ->first();

    $c_test = collect([
      'name' => 'テスト',
    ]);

    var_dump($q_first);

    dd($e_all, $q_get, $q_first, $c_test);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
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

## 50 Carbon 日付ライブラリ その 1

### Carbon

PHP の DateTime クラスを拡張した日付ライブラリ<br>
Laravel に標準搭載<br>

公式サイト<br>
https://carbon.nesbot.com/ <br>

個人ブログ<br>
https://coinbaby8.com/carbon-laravel.html <br>

エロクアントの timestamp は Carbon インスタンス<br>

`$eloquents->created_at->diffForHumans()`<br>

クエリビルダで Carbon を使うなら<br>

```
Carbon\Carbon::parse($query->created_at)
  ->diffForHumans();
```

### ハンズオン

- `app/Http/Controllers/Admin/OwnersController.php`を編集<br>

```php:OwnersController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner; // eloquent エロクアント
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // QueryBuilder クエリビルダ

class OwnersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:admin');
  }

  public function index()
  {
    // 追加
    $date_now = Carbon::now();
    $date_parse = Carbon::parse(now());
    echo $date_now . '<br>';
    echo $date_now->year . '<br>';
    echo $date_parse . '<br>';

    $e_all = Owner::all();
    $q_get = DB::table('owners')
      ->select('name')
      ->get();
    $q_first = DB::table('owners')
      ->select('name')
      ->first();

    $c_test = collect([
      'name' => 'テスト',
    ]);

    var_dump($q_first);

    dd($e_all, $q_get, $q_first, $c_test);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
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
