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
