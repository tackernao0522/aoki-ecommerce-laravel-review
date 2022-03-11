# セクション 04: マルチログイン対応

## 33 モデルとマイグレーション

### マルチログイン URL

ユーザー（商品を買う）・・/<br>
オーナー（商品を登録）・・/owner/<br>
管理者（オーナーの管理）・/admin/<br>

### マルチログイン手順

1. モデル、マイグレーションファイル作成<br>
2. ルート設定<br>
3. ルートサービスプロバイダ設定<br>
4. ガード設定<br>
5. ミドルウェア設定<br>
6. リクエストクラス設定<br>
7. コントローラー&ブレード作成<br>

### 1. モデルとマイグレーション生成

php artisan make:model Owner -m<br>
php artisan make:model Admin -m<br>

-m でマイグレーションファイルも生成<br>

`app/models`フォルダ以下に生成される<br>
Authenticatable を継承<br>

### ハンズオン

- `$ php artisan make:model Owner -m`を実行<br>

* `$ php artisan make:model Admin -m`を実行<br>

- `app/Models/Owner.php`を編集<br>

```php:Owner.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Owner extends Authenticatable
{
  use HasFactory;

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
}
```

- `app/Models/Admin.php`を編集<br>

```php:Admin.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{
  use HasFactory;

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
}
```

- `database/migrations/create_owners_table.php`を編集<br>

```php:create_owners_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('owners', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->rememberToken();
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
    Schema::dropIfExists('owners');
  }
}
```

- `database/migrations/create_admins_table.php`を編集<br>

```php:create_admins_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('admins', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->rememberToken();
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
    Schema::dropIfExists('admins');
  }
}
```

- `$ php artisan migrate`を実行<br>

## 34 マイグレーション(パスワードリセット)

### 1. マイグレーション設定

create_users_table の内容を<br>
owner, admin にそれぞれコピーする<br>

php artisan make:migration create_owner_password_resets --create=owner_password_resets<br>
php artisan make:migration create_admin_password_resets --create=admin_password_resets<br>
password_resets の内容をそれぞれコピーする<br>

### ハンズオン

- `$ php artisan make:migration create_owner_password_resets --create=owner_password_resets`を実行<br>

- `$ php artisan make:migration create_admin_password_resets --create=admin_password_resets`を実行<br>

* `database/migrations/create_owner_password_resets.php`を編集<br>

```php:create_owner_password_resets.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnerPasswordResets extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('owner_password_resets', function (Blueprint $table) {
      $table->string('email')->index();
      $table->string('token');
      $table->timestamp('created_at')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('owner_password_resets');
  }
}
```

- `database/migrations/create_admin_password_resets.php`を編集<br>

```php:create_admin_password_resets.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPasswordResets extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('admin_password_resets', function (Blueprint $table) {
      $table->string('email')->index();
      $table->string('token');
      $table->timestamp('created_at')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('admin_password_resets');
  }
}
```

- `$ php artisan migrate`を実行<br>
