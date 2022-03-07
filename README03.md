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
