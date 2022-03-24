## 71 Shop コントローラミドルウェア

### Shop ルートパラメータの注意

`owner/shops/edit/2/`<br>

edit, update など URL にパラメータを使う場合 URL の数値を直接変更すると<br>

他のオーナーの Shop が見れてしまう・・NG<br>

ログイン済みオーナーの Shop URL でなければ 404 を表示<br>

https://readouble.com/laravel/8.x/ja/controllers.html (コントローラミドルウェア)<br>

### Shop ミドルウェア設定

コンストラクタ内<br>

```php:ShopController.php
$this->middleware(function ($request, $next) {
  $id = $request->route()->parameter('shop'); // shopのid取得
  if (!is_null($id)) {
    // null判定
    $shopsOwnerId = Shop::findOrFail($id)->owner->id;
    $shopId = (int) $shopsOwnerId; // キャスト 文字列ー>数値に型変換
    $ownerId = Auth::id();
    if ($shopId !== $ownerId) {
      // 同じでなかったら
      abort(404); // 404画面表示
    }
  }
});
```

### ハンズオン

- app/Http/Controllers/Owner/ShopController.php`を編集<br>

```php:ShopController.php
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:owners');

    $this->middleware(function ($request, $next) {
      // dd($request->route()->parameter('shop')); // 文字列
      // // dd(Auth::id()); // 数字
      // return $next($request);
      $id = $request->route()->parameter('shop'); // shopのid取得
      if (!is_null($id)) {
        // null判定
        $shopsOwnerId = Shop::findOrFail($id)->owner->id;
        $shopId = (int) $shopsOwnerId; // キャスト 文字列ー>数値に型変換
        $ownerId = Auth::id();
        if ($shopId !== $ownerId) {
          // 同じでなかったら
          abort(404); // 404画面表示
        }
      }

      return $next($request);
    });
  }

  public function index()
  {
    $ownerId = Auth::id();
    $shops = Shop::where('owner_id', $ownerId)->get();

    return view('owner.shops.index', compact('shops'));
  }

  public function edit($id)
  {
    dd(Shop::findOrFail($id));
  }

  public function update(Request $request, $id)
  {
  }
}
```
