## 126 Cart(カート) 多対多<br>

複数のユーザーが複数の商品を持てる・・多対多　(User[多]<->Product[多])<br>

中間(pivot)テーブルをはさみ・・1 対多 (User[1]->Cart[多]<-Product[1])<br>

自動で生成するなら product_user(アルファベット順)<br>
今回は Cart というモデルを作成し設定<br>

### カート 多対多

```php:Cart.php
protected $fillable = [
  'user_id',
  'product_id',
  'quantity',
];
```

### カート 多対多 リレーション設定

https://readouble.com/laravel/9.x/ja/eloquent-relationships.html#many-to-many <br>

`User.php`<br>

```php:User.php
public function products()
{
  return $this->belongsToMany(Product::class, 'carts')
    ->withPivot(['id', 'quantity']);
  // 第2引数で中間テーブル名
  // 中間テーブルのカラム取得
  // デフォルトでは関連付けるカラム(user_idとproduct_idのみ取得)
}
```

`Product.php`<br>

```php:Product.php
public function users()
{
  return $this->belongsToMany(User::class, 'carts')
    ->withPivot(['id', 'quantity']);
}
```

### ハンズオン

- `$ php artisan make:model Cart -m`を実行<br>

* `database/migrations/create_carts_table.php`を編集<br>

```php:create_carts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('carts', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('user_id')
        ->constrained()
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table
        ->foreignId('product_id')
        ->constrained()
        ->onUpdate('cascade')
        ->onDelete('cascade');
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
    Schema::dropIfExists('carts');
  }
}
```

- `$ php artisan migrate`を実行<br>

* `app/Models/Cart.php`を編集<br>

```php:Cart.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
  use HasFactory;

  protected $fillable = ['user_id', 'product_id', 'quantity'];
}
```

- `app/Models/User.php`を編集<br>

```php:User.php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// 追加
use App\Models\Product;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

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

  // 追加
  public function products()
  {
    return $this->belongsToMany(Product::class, 'carts')->withPivot([
      'id',
      'quantity',
    ]);
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
use App\Models\SecondaryCategory;
use App\Models\Image;
use App\Models\Stock;
// 追加
use App\Models\User;

class Product extends Model
{
  use HasFactory;

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

  public function imageSecond()
  {
    return $this->belongsTo(Image::class, 'image2', 'id');
  }

  public function imageThird()
  {
    return $this->belongsTo(Image::class, 'image3', 'id');
  }

  public function imageFourth()
  {
    return $this->belongsTo(Image::class, 'image4', 'id');
  }

  public function stocks()
  {
    return $this->hasMany(Stock::class);
  }

  // 追加
  public function users()
  {
    return $this->belongsToMany(User::class, 'carts')->withPivot([
      'id',
      'quantity',
    ]);
  }
}
```
