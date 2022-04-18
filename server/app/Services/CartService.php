<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;

class CartService
{
  public static function getItemsInCart($items)
  {
    $products = [];

    // dd($items);
    foreach ($items as $item) { // カート内の商品を一つずつ処理
      $p = Product::findOrFail($item->product_id);
      $owner = $p->shop->owner->select('name', 'email')
        ->first()->toArray();
      $values = array_values($owner);
      $keys = ['ownerName', 'email'];
      $ownerInfo = array_combine($keys, $values);
      // dd($ownerInfo);
      $product = Product::where('id', $item->product_id)
        ->select('id', 'name', 'price')->get()->toArray();
      $quantity = Cart::where('product_id', $item->product_id)
        ->select('quantity')->get()->toArray();
      // dd($ownerInfo, $product, $quantity);
      $result = array_merge($product[0], $ownerInfo, $quantity[0]);
      // dd($result);
      array_push($products, $result);
    }
    // dd($products);
    return $products;
  }
}
