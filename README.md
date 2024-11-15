# Section01: 紹介

## 03 講座の内容、Laravel の概要

### マルチログインの応用例

| サイトの種類 |        提供側(販売側)        |  利用側(購入側)  |
| :----------: | :--------------------------: | :--------------: |
|     物販     |          商品の登録          | 商品を探す・買う |
|    不動産    |          物件の登録          |    物件を探す    |
|     求人     |        求人情報の登録        |  求人情報を探す  |
|     副業     |         スキルの登録         |     依頼する     |
|   家電修理   | エアコンなどの修理内容を登録 |  探す・依頼する  |

## 04 Laravel のインストール

- 参考: https://readouble.com/laravel/8.x/ja/installation.html <br>

## 05 DB 設定、マイグレート

### 初期設定

Mysql DB 作成<br>
タイムゾーン、言語設定 config/app.php<br>
.env 設定(環境ファイル)<br>
バリデーションの言語ファイル<br>
デバッグバー<br>

- `$ php artisan migrate`を実行<br>

## 06 Git/GitHub の設定

- 既に設定済み(動画参照)<br>

## 07 初期設定

### config/app.php タイムゾーン、言語設定

- `config/app.php`を編集<br>

```php:app.php
<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

  'name' => env('APP_NAME', 'Laravel'),

  /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

  'env' => env('APP_ENV', 'production'),

  /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

  'debug' => (bool) env('APP_DEBUG', false),

  /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

  'url' => env('APP_URL', 'http://localhost'),

  'asset_url' => env('ASSET_URL', null),

  /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

  // 編集
  'timezone' => 'Asia/Tokyo',

  /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

  // 編集
  'locale' => 'ja',

  /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

  'fallback_locale' => 'en',

  /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

  'faker_locale' => 'en_US',

  /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

  'key' => env('APP_KEY'),

  'cipher' => 'AES-256-CBC',

  /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

  'providers' => [
    /*
     * Laravel Framework Service Providers...
     */
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    Illuminate\Bus\BusServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
    Illuminate\Cookie\CookieServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Encryption\EncryptionServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Foundation\Providers\FoundationServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Mail\MailServiceProvider::class,
    Illuminate\Notifications\NotificationServiceProvider::class,
    Illuminate\Pagination\PaginationServiceProvider::class,
    Illuminate\Pipeline\PipelineServiceProvider::class,
    Illuminate\Queue\QueueServiceProvider::class,
    Illuminate\Redis\RedisServiceProvider::class,
    Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,

    /*
     * Package Service Providers...
     */

    /*
     * Application Service Providers...
     */
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
  ],

  /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

  'aliases' => [
    'App' => Illuminate\Support\Facades\App::class,
    'Arr' => Illuminate\Support\Arr::class,
    'Artisan' => Illuminate\Support\Facades\Artisan::class,
    'Auth' => Illuminate\Support\Facades\Auth::class,
    'Blade' => Illuminate\Support\Facades\Blade::class,
    'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
    'Bus' => Illuminate\Support\Facades\Bus::class,
    'Cache' => Illuminate\Support\Facades\Cache::class,
    'Config' => Illuminate\Support\Facades\Config::class,
    'Cookie' => Illuminate\Support\Facades\Cookie::class,
    'Crypt' => Illuminate\Support\Facades\Crypt::class,
    'Date' => Illuminate\Support\Facades\Date::class,
    'DB' => Illuminate\Support\Facades\DB::class,
    'Eloquent' => Illuminate\Database\Eloquent\Model::class,
    'Event' => Illuminate\Support\Facades\Event::class,
    'File' => Illuminate\Support\Facades\File::class,
    'Gate' => Illuminate\Support\Facades\Gate::class,
    'Hash' => Illuminate\Support\Facades\Hash::class,
    'Http' => Illuminate\Support\Facades\Http::class,
    'Js' => Illuminate\Support\Js::class,
    'Lang' => Illuminate\Support\Facades\Lang::class,
    'Log' => Illuminate\Support\Facades\Log::class,
    'Mail' => Illuminate\Support\Facades\Mail::class,
    'Notification' => Illuminate\Support\Facades\Notification::class,
    'Password' => Illuminate\Support\Facades\Password::class,
    'Queue' => Illuminate\Support\Facades\Queue::class,
    'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
    'Redirect' => Illuminate\Support\Facades\Redirect::class,
    // 'Redis' => Illuminate\Support\Facades\Redis::class,
    'Request' => Illuminate\Support\Facades\Request::class,
    'Response' => Illuminate\Support\Facades\Response::class,
    'Route' => Illuminate\Support\Facades\Route::class,
    'Schema' => Illuminate\Support\Facades\Schema::class,
    'Session' => Illuminate\Support\Facades\Session::class,
    'Storage' => Illuminate\Support\Facades\Storage::class,
    'Str' => Illuminate\Support\Str::class,
    'URL' => Illuminate\Support\Facades\URL::class,
    'Validator' => Illuminate\Support\Facades\Validator::class,
    'View' => Illuminate\Support\Facades\View::class,
  ],
];
```

### デバッグバーのインストール

- `$ composer require barryvdh/laravel-debugbar`を実行<br>

※ デバッグモードの切り替えは `server/.env`ファイルの`APP_DEBUG=true`の部分を`true` or `false`に変更する<br>

# セクション 02: Laravel Breeze + Blade Component

## 08 Laravel Breeze の紹介

### 認証ライブラリ比較

|              |                                Larvel / ui                                 |                               Laravel Breeze                               |                   Fortify                    |       Jetstream       |
| :----------: | :------------------------------------------------------------------------: | :------------------------------------------------------------------------: | :------------------------------------------: | :-------------------: |
|   Version    |                                   6.x〜                                    |                                   8.x〜                                    |                    8.x〜                     |         8.x〜         |
| View（PHP）  |                                   Blade                                    |                                   Blade                                    |                      -                       |   Livewire + Blade    |
|      JS      |                             Vue.js / React.js                              |                                 Alpine.js                                  |                      -                       |  inertia.js + Vue.js  |
|     CSS      |                                 Bootstrap                                  |                                Tailwindcss                                 |                      -                       |      Tailwindcss      |
| 追加ファイル |                           View/Controller/Route                            |                           View/Controller/Route                            |                      -                       | View/Controller/Route |
|    機能 1    | ログイン、ユーザー登録、パスワードリセット、<br>メール検証、パスワード確認 | ログイン、ユーザー登録、パスワードリセット、<br>メール検証、パスワード確認 |                      -                       |
|    機能 2    |                                     -                                      |                                     -                                      | 2 要素認証、<br>プロフィール管理、チーム管理 | API サポート(Sanctum) |

### Laravel Breeze インストール

- `例`<br>

`$ composer require laravel/breeze "1.*" --dev`<br>

`$ php artisan breeze:install`<br>

`$ npm install && npm run dev`<br>

マニュアル: スターターキット https://readouble.com/laravel/8.x/ja/starter-kits.html#laravel-breeze<br>

## 09 Laravel Breeze のインストール

### ハンズオン

- `$ composer require laravel/breeze "1.*" --dev`を実行<br>

* `$ php artisan breeze:install`を実行<br>

- `$ npm install`を実行<br>

* `$ npm run dev`を実行<br>

### Laravel Breeze 追加ファイル(抜粋)

app/Http/Controllers/Auth<br>
app/Http/Controllers/Requests/Auth<br>
app/View/Components<br>
routes/web.php<br>
routes/auth.php<br>
resources/views/auth<br>
resources/views/components<br>

## 10 表示の流れ、ルーティング

### ブラウザに表示されるまでの流れ

`クライアント` -> `index.php` -> `ミドルウェア` -> `ルーティング` -> `コントローラ` -> `モデル(データベース)` -> `ビュー` -> クライアント<br>

※ `クライアント`と`ミドルウェア`の間には`サービスコンテナ`やサービスプロパイダ` etc...

MVC モデル: Model, View, Controller<br>

### 認証機能、追加ファイル

サービスプロバイダ<br>
`config/app.php`内の`providers`, `aliases`に`Auth`と記載<br>

ルーティング<br>
`routes/web.php`<br>
`routes/auth.php`<br>

### ルートファイル

```
use Illuminate\Support\Facades\Route; // Routeを読み込む
use App\Http\Controllers\Auth\RegisteredUserController; // コントローラを読み込む

Route::get('/register', // Route::getかpost (url) [RegisteredUserController::class, 'create']) // []でコントローラ名、メソッド名->middleware('guest') // middleware guestだったら->name('register); // 名前付きルート
```

マニュアル： 認証 https://readouble.com/laravel/8.x/ja/authentication.html<br>
->ルートの保護、リダイレクト、ガードの指定、ログイン回数制限<br>

## 11 ルート->コントローラ->ビュー

### コントローラファイル群

`app/Http/Controllers/Auth`<br>

```
authenticatedSession // 認証セッション
confirmablePassword // パスワード確認
emailVerificationNotification // メール検証通知
emailVerificationPrompt // メール検証プロンプト
newPassword // 新しいパスワード
passwordResetLink // パスワードリセットリンク
registeredUser // ユーザー登録
verifyEmail // メール検証
```

### ビューファイル群

`resources/views/auth`<br>

```
confirm-password // パスワード確認
forgot-password // パスワード忘れ
login // ログイン
register // 新規登録
reset-password // パスワードリセット
verify-email // メール検証
```

blade ファイルは xxx.blade.php の形式で作成する<br>

## 12 日本語化対応

- 参考: https://readouble.com/laravel/8.x/ja/validation-php.html <br>

### Laravel Breeze 日本語化

https://readouble.com/laravel/8.x/ja/validation-php.html <br>

ダウンロード後<br>
`Lang-master/src/ja`フォルダを`resources/lang`フォルダにコピー<br>

`例`<br>

`resources/lang/ja/validation.php`を調整<br>

```php:validation.php
'attributes' => [
  'name' => '名前', // 追加
  'email' => 'メールアドレス', // 追加
  'password' => 'パスワード' // 追加
],
```

### ハンズオン

- `resources/lang/ja/validation.php`を編集<br>

```php:validation.php
<?php

/*
|--------------------------------------------------------------------------
| Validation Language Lines
|--------------------------------------------------------------------------
|
| The following language lines contain the default error messages used by
| the validator class. Some of these rules have multiple versions such
| as the size rules. Feel free to tweak each of these messages here.
|
*/

return [
  'accepted' => ':attributeを承認してください。',
  'accepted_if' => 'The :attribute must be accepted when :other is :value.',
  'active_url' => ':attributeは、有効なURLではありません。',
  'after' => ':attributeには、:dateより後の日付を指定してください。',
  'after_or_equal' => ':attributeには、:date以降の日付を指定してください。',
  'alpha' => ':attributeには、アルファベッドのみ使用できます。',
  'alpha_dash' =>
    ':attributeには、英数字(\'A-Z\',\'a-z\',\'0-9\')とハイフンと下線(\'-\',\'_\')が使用できます。',
  'alpha_num' =>
    ':attributeには、英数字(\'A-Z\',\'a-z\',\'0-9\')が使用できます。',
  'array' => ':attributeには、配列を指定してください。',
  'before' => ':attributeには、:dateより前の日付を指定してください。',
  'before_or_equal' => ':attributeには、:date以前の日付を指定してください。',
  'between' => [
    'array' => ':attributeの項目は、:min個から:max個にしてください。',
    'file' =>
      ':attributeには、:min KBから:max KBまでのサイズのファイルを指定してください。',
    'numeric' => ':attributeには、:minから、:maxまでの数字を指定してください。',
    'string' => ':attributeは、:min文字から:max文字にしてください。',
  ],
  'boolean' => ':attributeには、\'true\'か\'false\'を指定してください。',
  'confirmed' => ':attributeと:attribute確認が一致しません。',
  'current_password' => 'The password is incorrect.',
  'date' => ':attributeは、正しい日付ではありません。',
  'date_equals' => ':attributeは:dateに等しい日付でなければなりません。',
  'date_format' => ':attributeの形式は、\':format\'と合いません。',
  'declined' => 'The :attribute must be declined.',
  'declined_if' => 'The :attribute must be declined when :other is :value.',
  'different' => ':attributeと:otherには、異なるものを指定してください。',
  'digits' => ':attributeは、:digits桁にしてください。',
  'digits_between' => ':attributeは、:min桁から:max桁にしてください。',
  'dimensions' => ':attributeの画像サイズが無効です',
  'distinct' => ':attributeの値が重複しています。',
  'email' => ':attributeは、有効なメールアドレス形式で指定してください。',
  'ends_with' =>
    ':attributeは、次のうちのいずれかで終わらなければなりません。: :values',
  'enum' => 'The selected :attribute is invalid.',
  'exists' => '選択された:attributeは、有効ではありません。',
  'file' => ':attributeはファイルでなければいけません。',
  'filled' => ':attributeは必須です。',
  'gt' => [
    'array' => ':attributeの項目数は、:value個より大きくなければなりません。',
    'file' => ':attributeは、:value KBより大きくなければなりません。',
    'numeric' => ':attributeは、:valueより大きくなければなりません。',
    'string' => ':attributeは、:value文字より大きくなければなりません。',
  ],
  'gte' => [
    'array' => ':attributeの項目数は、:value個以上でなければなりません。',
    'file' => ':attributeは、:value KB以上でなければなりません。',
    'numeric' => ':attributeは、:value以上でなければなりません。',
    'string' => ':attributeは、:value文字以上でなければなりません。',
  ],
  'image' => ':attributeには、画像を指定してください。',
  'in' => '選択された:attributeは、有効ではありません。',
  'in_array' => ':attributeが:otherに存在しません。',
  'integer' => ':attributeには、整数を指定してください。',
  'ip' => ':attributeには、有効なIPアドレスを指定してください。',
  'ipv4' => ':attributeはIPv4アドレスを指定してください。',
  'ipv6' => ':attributeはIPv6アドレスを指定してください。',
  'json' => ':attributeには、有効なJSON文字列を指定してください。',
  'lt' => [
    'array' => ':attributeの項目数は、:value個より小さくなければなりません。',
    'file' => ':attributeは、:value KBより小さくなければなりません。',
    'numeric' => ':attributeは、:valueより小さくなければなりません。',
    'string' => ':attributeは、:value文字より小さくなければなりません。',
  ],
  'lte' => [
    'array' => ':attributeの項目数は、:value個以下でなければなりません。',
    'file' => ':attributeは、:value KB以下でなければなりません。',
    'numeric' => ':attributeは、:value以下でなければなりません。',
    'string' => ':attributeは、:value文字以下でなければなりません。',
  ],
  'mac_address' => 'The :attribute must be a valid MAC address.',
  'max' => [
    'array' => ':attributeの項目は、:max個以下にしてください。',
    'file' => ':attributeには、:max KB以下のファイルを指定してください。',
    'numeric' => ':attributeには、:max以下の数字を指定してください。',
    'string' => ':attributeは、:max文字以下にしてください。',
  ],
  'mimes' => ':attributeには、:valuesタイプのファイルを指定してください。',
  'mimetypes' => ':attributeには、:valuesタイプのファイルを指定してください。',
  'min' => [
    'array' => ':attributeの項目は、:min個以上にしてください。',
    'file' => ':attributeには、:min KB以上のファイルを指定してください。',
    'numeric' => ':attributeには、:min以上の数字を指定してください。',
    'string' => ':attributeは、:min文字以上にしてください。',
  ],
  'multiple_of' => ':attributeは:valueの倍数でなければなりません',
  'not_in' => '選択された:attributeは、有効ではありません。',
  'not_regex' => ':attributeの形式が無効です。',
  'numeric' => ':attributeには、数字を指定してください。',
  'password' => 'パスワードが正しくありません。',
  'present' => ':attributeが存在している必要があります。',
  'prohibited' => ':attributeフィールドは禁止されています。',
  'prohibited_if' =>
    ':attributeフィールドは、:otherが:valueの場合は禁止されています。',
  'prohibited_unless' =>
    ':attributeフィールドは、:otherが:valuesでない限り禁止されています。',
  'prohibits' => 'The :attribute field prohibits :other from being present.',
  'regex' => ':attributeには、有効な正規表現を指定してください。',
  'required' => ':attributeは、必ず指定してください。',
  'required_array_keys' =>
    'The :attribute field must contain entries for: :values.',
  'required_if' => ':otherが:valueの場合、:attributeを指定してください。',
  'required_unless' =>
    ':otherが:values以外の場合、:attributeを指定してください。',
  'required_with' =>
    ':valuesが指定されている場合、:attributeも指定してください。',
  'required_with_all' =>
    ':valuesが全て指定されている場合、:attributeも指定してください。',
  'required_without' =>
    ':valuesが指定されていない場合、:attributeを指定してください。',
  'required_without_all' =>
    ':valuesが全て指定されていない場合、:attributeを指定してください。',
  'same' => ':attributeと:otherが一致しません。',
  'size' => [
    'array' => ':attributeの項目は、:size個にしてください。',
    'file' => ':attributeには、:size KBのファイルを指定してください。',
    'numeric' => ':attributeには、:sizeを指定してください。',
    'string' => ':attributeは、:size文字にしてください。',
  ],
  'starts_with' => ':attributeは、次のいずれかで始まる必要があります。:values',
  'string' => ':attributeには、文字を指定してください。',
  'timezone' => ':attributeには、有効なタイムゾーンを指定してください。',
  'unique' => '指定の:attributeは既に使用されています。',
  'uploaded' => ':attributeのアップロードに失敗しました。',
  'url' => ':attributeは、有効なURL形式で指定してください。',
  'uuid' => ':attributeは、有効なUUIDでなければなりません。',
  'custom' => [
    'attribute-name' => [
      'rule-name' => 'custom-message',
    ],
  ],

  // 追記
  'attributes' => [
    'name' => '名前',
    'email' => 'メールアドレス',
    'password' => 'パスワード',
  ],
  //
];
```

- `resources/lang/ja.json`ファイルを作成<br>

```json:ja.json
{
  "Whoops! Something went wrong.": "おっと、サーバーで何か問題が発生しました。"
}
```

- 新規登録しておく<br>
