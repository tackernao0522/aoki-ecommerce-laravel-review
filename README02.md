## 13 tailwindcss の紹介

変更した場合は `$ npm run dev` or `$npm run watch`を実行<br>

### Tailwindcss ver3

2021 年 12 月にリリース<br>
Laravel Breeze でも ver3 インストールに変更<br>

Just-In-Time 機能<br>
必要なものを、必要な時に、必要なだけ->使ったクラスだけを出力する機能<br>

`tailwind.config.js`<br>

```js:tailwind.config.js
purge->contentに変更
ここに記載されているファイルでTailswindcssが使われていたら出力される

php artisan serveだけだと反映されない
npm run dev や npm run watchで反映
```

- 参考: https://biz.addisteria.com/laravel_tailwind_css_error/ <br>

* `resources/views/layouts/app.blade.php`を編集<br>

```html:lobin.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap"
    />

    <!-- Styles -->
    <!-- 編集 -->
    <link rel="stylesheet" href="{{ asset('css/app.css?20220305') }}" />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
  </head>
  <body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
      @include('layouts.navigation')

      <!-- Page Heading -->
      <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          {{ $header }}
        </div>
      </header>

      <!-- Page Content -->
      <main>
        {{ $slot }}
      </main>
    </div>
  </body>
</html>
```

- `resources/views/auth/login.blade.php`を編集<br>

```html:lobin.blade.php
<x-guest-layout>
  <x-auth-card>
    <x-slot name="logo">
      <a href="/">
        // 編集
        <x-application-logo class="w-20 h-20 fill-current text-blue-500" />
      </a>
    </x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Validation Errors -->
    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <!-- Email Address -->
      <div>
        <x-label for="email" :value="__('Email')" />

        <x-input
          id="email"
          class="block mt-1 w-full"
          type="email"
          name="email"
          :value="old('email')"
          required
          autofocus
        />
      </div>

      <!-- Password -->
      <div class="mt-4">
        <x-label for="password" :value="__('Password')" />

        <x-input
          id="password"
          class="block mt-1 w-full"
          type="password"
          name="password"
          required
          autocomplete="current-password"
        />
      </div>

      <!-- Remember Me -->
      <div class="block mt-4">
        <label for="remember_me" class="inline-flex items-center">
          <input
            id="remember_me"
            type="checkbox"
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            name="remember"
          />
          <span class="ml-2 text-sm text-gray-600">
            {{ __('Remember me') }}
          </span>
        </label>
      </div>

      <div class="flex items-center justify-end mt-4">
        @if (Route::has('password.request'))
        <a
          class="underline text-sm text-gray-600 hover:text-gray-900"
          href="{{ route('password.request') }}"
        >
          {{ __('Forgot your password?') }}
        </a>
        @endif

        <x-button class="ml-3">
          {{ __('Log in') }}
        </x-button>
      </div>
    </form>
  </x-auth-card>
</x-guest-layout>
```

### tailwind(追い風)css

CSS フレームワーク<br>

xUI キット ○ ユーティリティクラス<br>
カスタマイズ(微調整)しやすい<br>

Low レベル(CSS に近い)<br>
クラス名・・CSS プロパティ名に近い->CSS も身につく<br>
レスポンシブ対応 Flexbox, Grid 採用<br>

### レスポンシブ対応

同じコードで PC, SP, タブレットを表示<br>
ブラウザの幅に合わせて見やすく自動調整<br>

モバイルファースト(インデックス)<br>
2018 年->2020 年->2021 年<br>

https://www.itra.co.jp/webmedia/what-is-mfi.html <br>

### モバイルファースト

| Breakpoint prefix | Minimum width |               CSS                |
| :---------------: | :-----------: | :------------------------------: |
|        sm         |     640px     | @media (min-width: 640px) {...}  |
|        md         |     768px     | @media (min-width: 768px) {...}  |
|        lg         |    1024px     | @media (min-width: 1024px) {...} |
|        xl         |    1080px     | @media (min-width: 1080px) {...} |
|        2xl        |    1536px     | @media (min-width: 1536px) {...} |

全ての幅で共通 -> md (タブレット) -> lg（ノート PC）の順で作る<br>

`例`<br>

```
bg-green-300 md:bg-blue-300 lg:bg-red-300
全ての幅->md以上->lg以上

md:flex で md 以上で Flexbox 有効
```

## 15 Blade コンポーネントの紹介

### 機能比較

|                        |                     テンプレート継承                     |                                              コンポーネント                                              |
| :--------------------: | :------------------------------------------------------: | :------------------------------------------------------------------------------------------------------: |
|       バージョン       |                                                          |                                                  7.x〜                                                   |
|          用途          | (共通)ヘッダー、サイドバー、フッター<br>(個別)コンテンツ |                      共通部分の表示<br>データや属性の受け渡し<br>スロット(差し込み)                      |
|        メリット        |           PHP の require に似ている。シンプル            |                                        Controller と View の分離                                         |
|        ファイル        |              resources/views/xxx.blade.php               | app/view/components/xxx.php<br>resources/views/components/xxx.blade.php<br>resources/views/xxx.blade.php |
|      関連タグなど      |           @yield, @extend, @section, @include            |              x-コンポーネント名(ケバブケース)<br><x-alert type="error" :message="$message">              |
| 関連機能<br>(スロット) |                                                          |                     スロット {{$slot}}<br><x-alert>コンポーネント内の文字</x-alert>                      |

### Blade コンポーネント

|                    |                生成方法                 | view/component | resources/views/components | resources/views/ |
| :----------------: | :-------------------------------------: | :------------: | :------------------------: | :--------------: |
|    クラブベース    |     php artisan make:component xxx      |       ○        |             ○              |        ○         |
|     インライン     | php artisan make:component xxx --inline |       ○        |                            |        ○         |
| 匿名コンポーネント |            直接ファイル作成             |                |             ○              |        ○         |

## 16 準備(ルート->コントローラ->ビュー)

- `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ComponentTestController;
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

require __DIR__ . '/auth.php';
```

- `$ php artisan make:controller ComponentTestController`を実行<br>

```php:ComponentTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComponentTestController extends Controller
{
  public function showComponent1()
  {
    return view('tests.component-test1');
  }

  public function showComponent2()
  {
    return view('tests.component-test2');
  }
}
```

- `resources/views/tests`ディレクトリを作成<br>

- `redouces/views/tests/component-test1.blade.php` と `redouces/views/tests/component-test2.blade.php`ファイルを作成<br>

* `resources/views/tests/component-test1.blade.php`を編集<br>

```html:component-test1.blade.php
コンポーネントテスト1
```

- `resources/views/tests/component-test2.blade.php`を編集<br>

```html:component-test2.blade.php
コンポーネントテスト2
```

## 17 \$slot(スロット)

### Component のパターン

1 つのコンポーネント（部品）を複数ページで使える<br>
コンポーネント側を修正すると全て反映される<br>

### Component の書き方

`resources/views/components`ディレクトリ内に配置<br>

`<x-コンポーネント名></x-コンポーネント名>`<br>

フォルダで分けたい場合<br>

`resources/views/components/tests`ディレクトリの場合<br>

`<x-tests.コンポーネント名></x-tests.コンポーネント名>`<br>

- `resources/views/components/tests`ディレクトリを作成<br>

* `resources/views/components/tests/app.blade.php`ファイルを作成<br>

```html:app.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap"
    />

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
  </head>
  <body>
    <div class="font-sans text-gray-900 antialiased">
      {{ $slot }}
    </div>
  </body>
</html>
```

- `resources/views/tests/component-test1.blade.php`を編集<br>

```html:component-test1.blade.php
<x-tests.app>
  コンポーネントテスト1
</x-tests.app>
```

`resources/views/components/tests/app.blade.php`の{{ $slot }}の中に入る<br>

- `resources/views/tests/component-test2.blade.php`を編集<br>

```html:component-test2.blade.php
<x-tests.app>
  コンポーネントテスト2
</x-tests.app>
```

## 18 x-slot（名前付きスロット）

### Slot

Component 側<br>

```
{{ $slot }}
```

-> {{}} マスタッシュ構文（口ひげ）<br>

Blade 側<br>

```
<x-app>この文章が差し込まれる</x-app>
```

### 名前付き Slot

Component 側<br>

```
{{ $header }}
```

Blade 側<br>

```
<x-slot name="header">この文章が差し込まれる</x-slot>
```

- `resources/views/components/tests/app.blade.php`を編集<br>

```html:app.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap"
    />

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
  </head>

  <body>
    <header>
      {{ $header }}
    </header>
    <div class="font-sans text-gray-900 antialiased">
      {{ $slot }}
    </div>
  </body>
</html>
```

- `resources/views/tests/component-test1.blade.php`を編集<br>

```html:components-test1.blade.php
<x-tests.app>
  <x-slot name="header">
    ヘッダー1
  </x-slot>
  コンポーネントテスト1
</x-tests.app>
```

- `resources/views/tests/component-test1.blade.php`を編集<br>

```html:component-test2.blade.php
<x-tests.app>
  <x-slot name="header">ヘッダー2</x-slot>
  コンポーネントテスト2
</x-tests.app>
```

## 19 データの受け渡し方法（属性)

### Blade ファイルと Component

|                              |              Blade ファイル               |                     Blade コンポーネント                      |
| :--------------------------: | :---------------------------------------: | :-----------------------------------------------------------: |
|            \$slot            |         <x-app>ここに文字</x-app>         |                          {{ $slot }}                          |
|        名前付き slot         | <x-slot name="header">ここに文字</x-slot> |                         {{ $header }}                         |
|         属性(props)          |       <x-card message="メッセージ">       |                        {{ $message }}                         |
|             変数             |       <x-card :message="$message">        |                    コントローラなどに指定                     |
| 初期値<br>@props<br>連想配列 |     設定しない場合初期値が表示される      |             @props(['message' => '初期値です。'])             |
|  クラスの設定<br>属性ばっぐ  |          <div {{ $attributes }}>          | <div {{ $attributes->merge([ 'class' => 'text-sm']) }}></div> |

- `resources/views/components/tests/card.blade.php`ファイルを作成<br>

```html:card.blade.php
<div class="border-2 shadow-md w-1/4 p-2">
  <div>{{ $title }}</div>
  <div>画像</div>
  <div>{{ $content }}</div>
</div>
```

- `resources/views/tests/component-test1.blade.php`を編集<br>

```html:component-test1.blade.php
<x-tests.app>
  <x-slot name="header">
    ヘッダー1
  </x-slot>
  コンポーネントテスト1

  <x-tests.card title="タイトル1" content="本文1" />
</x-tests.app>
```

## 19 データの受け渡し方法（変数)

### コントローラなどから変数を渡す

コントローラ側<br>

```
$messegae = 'メッセージ';

return view('ビューファイル', compact('message'));
```

Blade 側<br>

```
<x-card :message="$message" />
```

- `Controllers/ComponentTestController.php`を編集<br>

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
    return view('tests.components-test2');
  }
}
```

- `resources/views/components/tests/card.blade.php`を編集<br>

```html:card.blade.php
<div class="border-2 shadow-md w-1/4 p-2">
  <div>{{ $title }}</div>
  <div>画像</div>
  <div>{{ $content }}</div>
  // 追記
  <div>{{ $message }}</div>
</div>
```

- `resources/views/tests/component-test1.blade.php`を編集<br>

```html:component-test1.blade.php
<x-tests.app>
  <x-slot name="header">
    ヘッダー1
  </x-slot>
  コンポーネントテスト1

  <x-tests.card title="タイトル1" content="本文1" :message="$message" />
</x-tests.app>
```
