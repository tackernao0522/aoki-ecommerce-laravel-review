# セクション 05: 管理者側

## 45 設計資料の紹介

### 要件定義〜基本設計

企画->要件定義->設計->実装->テスト->リリース<br>

上流工程 5 割 実装 2〜3 割<br>

https://qiita.com/Saku731/items/741fcf0f40dd989ee4f8 <br>

### 基本設計

画面設計(UI 設計)<br>
AdobeXD、Figma<br>
->今回は tailblock のテンプレートで作成<br>
https://tailblocks.cc/ <br>

機能設計 ・・ テーブル設計、ER 図<br>

### 基本設計リンク

URL 設計、テーブル設計、機能設計<br>
(Google スプレッドシート)<br>
https://docs.google.com/spreadsheets/d/1YIDqTKH2v2-n97kb2GNhWrcMGnJD84JMqTuzD_poMqo/edit#gid=0 <br>

ER 図(draw.id)<br>
https://drive.google.com/file/d/18sEk5LC-jJum-NU9JKNZibGRVX81aWE1/view <br>

## 46 アプリ名、ロゴ設定

アプリ名 ・・ .env ファイル<br>
APP_NAME= Umarche<br>

`Config/app.php`内で設定される<br>

ロゴ（ロゴ 作成 無料 などで検索）<br>
https://drive.google.com/file/d/1C2ooEDTFPp5cWr2B6gsYQnwKEhM3NmD3/view <br>

### ハンズオン

`.env`ファイルを編集<br>

```:.env
APP_NAME=Umarche # 編集
APP_ENV=local
APP_KEY=base64:kIiIWzZ988XaZKYB6TaM6fxktugDUr5fKaPJN0k2drk=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=ecommercedb-host
DB_PORT=3306
DB_DATABASE=ecommerce-database
DB_USERNAME=udemy_ecommerce
DB_PASSWORD=5t5a7k3a

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"

#Sendgrid用
MAIL_DRIVER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=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
MAIL_ENCRYPTION=tls
MAIL_FROM_NAME=Demo_funclub
MAIL_FROM_ADDRESS=takaki_5573031@yahoo.co.jp

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### ロゴ表示

`public`フォルダに直接置く ・・ 初期ファイル<br>
`storage`フォルダ ・・ フォルダ内画像は gitHub にアップしない<br>

表側(public)から見れるようにリンク<br>
`php artisan storage:link`<br>
`public/storage`リンクが生成される<br>

`asset()`ヘルパ関数で public 内のファイルを指定<br>

`asset("images/logo.png")を`components/application-logo.blade.php`に記載<br>

### ハンズオン

#### storage を使う場合

- `logo.png`ファイル`を`storage/app/public`ディレクトリに配置<br>

* `$ php artisan storage:link`を実行<br>

#### 今回は `public/images`ディレクトリを作成して`public/images/logo.png`で配置する<br>

- `resources/views/components/application-logo.blade.php`を編集<br>

```html:application-logo.blade.php
<img src="{{ asset("images/logo.png") }}">
```

- `resources/views/admin/auth/login.blade.php`を編集<br>

```html:login.blade.php
<x-guest-layout>
  <x-auth-card>
    管理者用
    <x-slot name="logo">
      <!-- 編集 -->
      <div class="w-28">
        <a href="/">
          <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
        </a>
      </div>
    </x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Validation Errors -->
    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <form method="POST" action="{{ route('admin.login') }}">
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
        @if (Route::has('admin.password.request'))
        <a
          class="underline text-sm text-gray-600 hover:text-gray-900"
          href="{{ route('admin.password.request') }}"
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
