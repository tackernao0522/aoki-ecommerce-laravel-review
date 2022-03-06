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
