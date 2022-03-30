## 91 Product リレーション

リレーション設定で第 2 引数に FK、<br>
第 3 引数で親モデル名を設定可能<br>
カラム名と同じメソッドは指定できないので名称変更<br>

`Product.php`<br>

```php:Product.php
public function category()
{
  return $this->belongsTo(SecondaryCategory::class, 'secondary_category_id');
}

public function imageFirst()
{
  return $this->belongsTo(Image::class, 'image1', 'id');
}
```

### ハンズオン

- `app/Models/Product.php`を編集<br>

```php:Product.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
// 追加
use App\Models\SecondaryCategory;
use App\Models\Image;
// ここまで

class Product extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function shop()
  {
    return $this->belongsTo(Shop::class);
  }

  // 追加
  public function category()
  {
    return $this->belongsTo(SecondaryCategory::class, 'secondary_category_id');
  }

  public function imageFirst()
  {
    return $this->belongsTo(Image::class, 'image1', 'id');
  }
  // ここまで
}
```

- `$ php artisan tinker`を実行<br>

* `>>> $product = new App\Models\Product`を実行<br>

- `$product->all()`を実行<br>

```:terminal
=> Illuminate\Database\Eloquent\Collection {#4608
     all: [
       App\Models\Product {#4609
         id: 1,
         shop_id: 1,
         secondary_category_id: 1,
         image1: 1,
         created_at: null,
         updated_at: null,
       },
       App\Models\Product {#4610
         id: 2,
         shop_id: 1,
         secondary_category_id: 2,
         image1: 2,
         created_at: null,
         updated_at: null,
       },
       App\Models\Product {#4611
         id: 3,
         shop_id: 1,
         secondary_category_id: 3,
         image1: 3,
         created_at: null,
         updated_at: null,
       },
       App\Models\Product {#4612
         id: 4,
         shop_id: 1,
         secondary_category_id: 4,
         image1: 3,
         created_at: null,
         updated_at: null,
       },
       App\Models\Product {#4613
         id: 5,
         shop_id: 1,
         secondary_category_id: 5,
         image1: 4,
         created_at: null,
         updated_at: null,
       },
     ],
   }
```

- `>>> $product::findOrFail(1)`を実行<br>

```:terminal
=> App\Models\Product {#4606
     id: 1,
     shop_id: 1,
     secondary_category_id: 1,
     image1: 1,
     created_at: null,
     updated_at: null,
   }
```

- `>>> $product::findOrFail(1)->shop`を実行<br>

```:terminal
=> App\Models\Shop {#4621
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

- `$product::findOrFail(1)->shop->owner`を実行<br>

```:terminal
=> App\Models\Owner {#4629
     id: 1,
     name: "Kaira",
     email: "takaproject777@gmail.com",
     email_verified_at: null,
     #password: "$2y$10$H6WT0X/RK2bVJPCXAsQixuBDaeY7ztGoVnW0wY0GJkCKdTQSdqKy6",
     #remember_token: null,
     created_at: "2022-03-16 11:11:11",
     updated_at: null,
     deleted_at: null,
   }
```

- `$product::findOrFail(1)->shop->owner->id`を実行<br>

```:terminal
=> 1
```

- `>>> $product::findOrFail(1)->category`を実行<br>

```:terminal
=> App\Models\SecondaryCategory {#4604
     id: 1,
     primary_category_id: 1,
     name: "靴",
     sort_order: 1,
     created_at: null,
     updated_at: null,
   }
```

- `>>> $product::findOrFail(1)->imageFirst`を実行<br>

```:terminal
=> App\Models\Image {#4612
     id: 1,
     owner_id: 1,
     filename: "sample1.jpg",
     title: null,
     created_at: null,
     updated_at: null,
   }
```

- `>>> $product::findOrFail(1)->imageFirst->filename`を実行<br>

```:terminal
=> "sample1.jpg
```
