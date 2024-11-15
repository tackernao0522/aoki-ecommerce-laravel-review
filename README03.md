## 21 初期値の設定方法(@props)

- `resources/views/tests/component-test1.blade.php`を編集<br>

```html:component-test1.blade.php
<x-tests.app>
  <x-slot name="header">
    ヘッダー1
  </x-slot>
  コンポーネントテスト1

  <x-tests.card title="タイトル1" content="本文1" :message="$message" />
  <x-tests.card title="タイトル2" />
</x-tests.app>
```

- `resources/views/components/tests/card.blade.php`を編集<br>

```html:card.blade.php
@props(['title', 'message' => '初期値です。', 'content' => '本文初期値です。'])

<div class="border-2 shadow-md w-1/4 p-2">
  <div>{{ $title }}</div>
  <div>画像</div>
  <div>{{ $content }}</div>
  <div>{{ $message }}</div>
</div>
```

## 22 属性バッグ(\$attribute)

- `resources/views/tests/component-test1.blade.php`を編集<br>

```html:component-test1.blade.php
<x-tests.app>
  <x-slot name="header">
    ヘッダー1
  </x-slot>
  コンポーネントテスト1

  <x-tests.card title="タイトル1" content="本文1" :message="$message" />
  <x-tests.card title="タイトル2" />
  <x-tests.card title="CSSを変更したい" class="bg-red-300" />
</x-tests.app>
```

- `resources/views/components/tests/card.blade.php`を編集<br>

```html:card.blade.php
@props(['title', 'message' => '初期値です。', 'content' => '本文初期値です。'])

<div {{ $attributes->
  merge([ 'class' => 'border-2 shadow-md w-1/4 p-2', ]) }}>
  <div>{{ $title }}</div>
  <div>画像</div>
  <div>{{ $content }}</div>
  <div>{{ $message }}</div>
</div>
```

## 23 クラスベースのコンポーネント

### Blade コンポーネント

|                    |                生成方法                 | View/Component | resources/views/components | resources/views/ |
| :----------------: | :-------------------------------------: | :------------: | :------------------------: | :--------------: |
|    クラスベース    |     php artisan make:component xxx      |       ○        |             ○              |        ○         |
|     インライン     | php artisan make:component xxx --inline |       ○        |                            |        ○         |
| 匿名コンポーネント |            直接ファイル作成             |                |             ○              |        ○         |

- `$ php artisan make:component TestClassBase`を実行<br>

* `resources/views/components/test-class-base.blade.php`ファイルを`test-class-base-component.blade.php`にリネームする<br>

- `app/View/Components/TestClassBase.php`を編集<br>

```php:TestClassBase.php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TestClassBase extends Component
{
  /**
   * Create a new component instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\Contracts\View\View|\Closure|string
   */
  public function render()
  {
    // 編集
    return view('components.tests.test-class-base-component');
  }
}
```

- `$ mv resources/views/components/test-class-base-component.blade.php resources/views/components/tests/test-class-base-component.blade.php`を実行(ファイル移動)<br>

* `resources/views/components/tests/test-class-base-component.blade.php`を編集<br>

```html:test-class-base-component.blade.php
<div>
  クラスベースのコンポーネントです。
  <!-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius -->
</div>
```

### クラスベースのコンポーネント

`App/View/Components`内のクラスを指定する<br>

クラス名 ・・・ TestClassBase（パスカルケース）<br>
Blade 内 ・・・ x-test-class-base（ケバブケース）<br>

コンポーネントクラス内で<br>

```
public function render() {
  return view('bladeコンポーネント名');
}
```

- `resources/views/tests/component-test2.blade.php`を編集<br>

```html:component-test2.blade.php
<x-tests.app>
  <x-slot name="header">ヘッダー2</x-slot>
  コンポーネントテスト2
  <x-test-class-base />
</x-tests.app>
```

- `app/Http/Controllers/ComponentTestController.php`を修正<br>

```php:ComponentTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComponentTestController extends Controller
{
  public function showComponent1()
  {
    $message = 'メッセージ123';

    return view('tests.component-test1', compact('message'));
  }

  public function showComponent2()
  {
    // 修正
    return view('tests.component-test2');
  }
}
```

## 24 クラスベースで属性・初期値を設定する

### 匿名とクラスベースの比較表

|                              |              Blade ファイル               | Blade コンポーネント(匿名)                                   |                                  クラスベース                                  |
| :--------------------------: | :---------------------------------------: | :----------------------------------------------------------- | :----------------------------------------------------------------------------: |
|            \$slot            |         <x-app>ここに文字</x-app>         | {{ $slot }}                                                  |                                      同左                                      |
|        名前付き slot         | <x-slot name="header">ここに文字</x-slot> | {{ $header }}                                                |                                      同左                                      |
|         属性(props)          |        <x-card title="タイトル" />        | {{ $title }}                                                 | public $title<br>public function __construct($title) { $this->title = $title } |
|             変数             |       <x-card :message="$title" />        | コントローラなどに指定 $message<br>{{ $message }}            |                                クラス内に指定可                                |
| 初期値<br>@props<br>連想配列 |   設定しない場合<br>初期値が表示される    | @props(['message' => '初期値です。'])                        |    public function \_\_construct($title="初期値") {$this->title = \$title}     |
|  クラスの設定<br>属性バッグ  |            class="bg-red-300"             | <div {{ $attributes->merge([ 'class' => 'text-sm'])}}></div> |                                      同左                                      |

- `resources/views/components/tests/test-class-base-component.blade.php`を編集<br>

```html:test-class-base-component.blade.php
<div>
  クラスベースのコンポーネントです。
  <div>{{ $classBaseMessage }}</div>
  <!-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius -->
</div>
```

- `resources/views/tests/component-test2.blade.php`を編集<br>

```html:component-test2.blade.php
<x-tests.app>
  <x-slot name="header">ヘッダー2</x-slot>
  コンポーネントテスト2
  <x-test-class-base classBaseMessage="メッセージです。" />
</x-tests.app>
```

- `app/View/Components/TestClassBase.php`を編集<br>

```php:TestClassBase.php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TestClassBase extends Component
{
  // 追記
  public $classBaseMessage;
  /**
   * Create a new component instance.
   *
   * @return void
   */
  // 編集
  public function __construct($classBaseMessage)
  {
    $this->classBaseMessage = $classBaseMessage;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\Contracts\View\View|\Closure|string
   */
  public function render()
  {
    return view('components.tests.test-class-base-component');
  }
}
```

- `$ php artisan view:clear`を実行してから localhost/component-test2 にアクセスしてみる<br>

* `app/View/Components/TestClassBase.php`を編集<br>

```php:TestClassBase.php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TestClassBase extends Component
{
  public $classBaseMessage;
  public $defaultMessage;
  /**
   * Create a new component instance.
   *
   * @return void
   */
  public function __construct(
    $classBaseMessage,
    // 追記
    $defaultMessage = '初期値です。'
  ) {
    $this->classBaseMessage = $classBaseMessage;
    // 追記
    $this->defaultMessage = $defaultMessage;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\Contracts\View\View|\Closure|string
   */
  public function render()
  {
    return view('components.tests.test-class-base-component');
  }
}
```

- `resources/views/components/tests/test-class-base-component.blade.php`を編集<br>

```html:test-class-base-component.blade.php
<div>
  クラスベースのコンポーネントです。
  <div>{{ $classBaseMessage }}</div>
  <div>{{ $defaultMessage }}</div>
  <!-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius -->
</div>
```

- `resources/views/tests/component-test2.blade.php`を編集<br>

```html:component-test2.blade.php
<x-tests.app>
  <x-slot name="header">ヘッダー2</x-slot>
  コンポーネントテスト2
  <x-test-class-base classBaseMessage="メッセージです。" />
  <div class="mb-4"></div>
  <x-test-class-base
    classBaseMessage="メッセージです。"
    defaultMessage="初期値から変更しています。"
  />
</x-tests.app>
```

## 26 Alpine.js の紹介

公式 GitHub<br>
https://github.com/alpinejs/alpine <br>

tailwind の JavaScript 版のようなもの(軽量)<br>

タグ内に専用のディレクティブを設置できる。<br>

```
<div x-show="isOpen()"></div>
```

### Alpine.js と Vue.js

|          Alipine.js          |     Vue.js      |                特徴                 |
| :--------------------------: | :-------------: | :---------------------------------: |
|            x-data            | data プロパティ | データの状態 オブジェクトでも書ける |
|            x-init            | mounted()フック |          DOM 更新時に実行           |
|            x-show            |     v-show      |            True なら表示            |
| x-bind:属性="式", :属性="式" |     v-bind      |           属性の値を設定            |
|   x-on:click="", @click=""   |      v-on       |   イベント時のメソッドなどを設定    |
|           x-model            |     v-model     |     双方向データバインディング      |
|        x-text, x-html        | v-text, v-html  |       テキスト表示、HTML 表示       |
|            x-ref             |                 |     コンポーネントから DOM 取得     |
|         x-if, x-for          |   v-if, v-for   |            if 文、for 文            |
|         x-transition         |  v-transition   |           トランジション            |
|           x-spread           |                 |   再利用できるオブジェクトに抽出    |
|           x-cloak            |     v-cloak     |            チラつき防止             |

# Section03 ライフサイクル

## 27 ライフサイクル その 1

### マルチログインの応用例

| サイトの種類 |          提供側(販売側)          |  利用側(購入側)  |
| :----------: | :------------------------------: | :--------------: |
|     物販     |            商品の登録            | 商品を探す・買う |
|    不動産    |            物件の登録            |    物件を探す    |
|     求人     |          求人情報の登録          |  求人情報を探す  |
|     副業     |           スキルの登録           |     依頼する     |
|   家電修理   | エアコンなどの<br>修理内容を登録 |  探す・依頼する  |

### マルチログイン 関連ファイル

model, controller, view, route<br>

`providers/RouteService/Provider.php`<br>
ログイン後の URL<br>

`config/auth.php` Guard 設定<br>
ログイン方法、どのテーブルを使うか<br>

`middleware/Authenticate.php`<br>
未認証時のリダイレクト処理<br>

`middleware/RedirectIfAuthenticated.php`<br>
ログイン済みユーザーのリダイレクト<br>

### ブラウザに表示されるまでの流れ

`クライアント` -> `index.php` -> `ミドルウェア` -> ルーティング -> コントローラ <-> `(モデル)(データベース)` -> ビュー -> `クライアント`<br>

MVC モデル： Model, View, Controller<br>

### 全体の流れ

WEB サーバが`public/index.php`にリダイレクト<br>

1. autoload 読み込み<br>
2. Application インスタンス作成(サービスコンテナ)<br>
3. HttpKernel インスタンス作成<br>
4. Request インスタンス作成<br>
5. HttpKernel がリクエストを処理して Response 取得<br>
6. レスポンス送信<br>
7. terminate()で後片付け<br>

### 1. autoload

require なしで別ファイルのクラスを利用可能<br>

### 2. サービスコンテナ

2. Application(サービスコンテナ)<br>
   `Bootstrap/app.php`を読み込み<br>
   `app.php`内<br>
   `$app = new Illumiate\Foundation\Application`<br>
   Application インスタンスが通称サービスコンテナ<br>

サービスプロバイダも読み込まれる<br>
`registerConfiguredProviders() {}`<br>

## 28 サービスコンテナ その 1

### サービスコンテナ

```
app()->bind()
app()->singleton()

app()->make()

dd(app()); で中身を確認できる

bindings: array:65
->65のサービスが設定されている
```

- `$ php artisan make:controller LifeCycleTestController`を実行<br>

- `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController; // 追記
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('welcome');
});

Route::get('/dashboard', function () {
  return view('dashboard');
})
  ->middleware(['auth'])
  ->name('dashboard'); // 認証しているかどうか

Route::get('/component-test1', [
  ComponentTestController::class,
  'showComponent1',
]);
Route::get('/component-test2', [
  ComponentTestController::class,
  'showComponent2',
]);
// 追記
Route::get('/servicecontainertest', [
  LifeCycleTestController::class,
  'showServiceContainerTest',
]);

require __DIR__ . '/auth.php';
```

- `app/Http/Controllers/LifeCycleTestController.php`を編集<br>

```php:LifeCycleTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
  public function showServiceContainerTest()
  {
    dd(app());
  }
}
```

- http://localhost/servicecontainertest にアクセスしてみる<br>

### サービスコンテナに登録する

```
app()->bind('lifeCycleTest', function() {
  return 'ライフサイクルテスト';
})
引数（取り出すときの名前, 機能）

Bindings:の数が65->66に増えている
```

- `app/Http/Controllers/LifeCycleTestController.php`を編集<br>

```php:LifeCycleTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
  public function showServiceContainerTest()
  {
    app()->bind('lifeCycleTest', function () {
      return 'ライフサイクルテスト';
    });

    dd(app());
  }
}
```

- http://localhost/servicecontainertest にアクセスしてみる<br>

bindings が 71 から 72 に増えていて`lifeCycleTest`が追加されている<br>

- `app/Http/Controllers/LifeCycleTestController.php`を編集<br>

```php:LifeCycleTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
  public function showServiceContainerTest()
  {
    app()->bind('lifeCycleTest', function () {
      return 'ライフサイクルテスト';
    });

    $test = app()->make('lifeCycleTest');

    dd($test, app());
  }
}
```

- http://localhost/servicecontainertest にアクセスしてみる<br>
