## 128 Cart 保存処理

### カートに追加

`CartController.php`<br>

```php:CartController.php
public function add(Request $request)
{
  $itemInCart = Cart::where('user_id', Auth::id())
    ->where('product_id', $request->product_id)->first(); // カートに商品がある確認

    if($itemInCart) {
      $itemInCart->quantity += $request->quantity; // あれば数量を追加
      $itemInCart->save();
    } else {
      Cart::create([ // なければ新規作成
        'user_id' => Auth::id(),
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
      ]);
    }

    return redirect()->route('user.cart.index);
}
```

### ハンズオン

- `app/Http/Controllers/User/CartController.php`を編集<br>

```php:CartController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class CartController extends Controller
{
  public function add(Request $request)
  {
    $itemInCart = Cart::where('user_id', Auth::id())
      ->where('product_id', $request->product_id)
      ->first();

    if ($itemInCart) {
      $itemInCart->quantity += $request->quantity;
      $itemInCart->save();
    } else {
      Cart::create([
        'user_id' => Auth::id(),
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
      ]);
    }

    dd('テスト');
  }
}
```
