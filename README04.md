## 29 サービスコンテナ その 2

### 依存関係の解決

依存した 2 つのクラス<br>
それぞれインスタンス化後に実行<br>

```
$message = new Message();
$sample = new Sample($message);
$sample->run();

サービスコンテナを使ったパターン
app()->bind('sample', Sample::class);
$sample = app()->make('sample');
$sample->run();
```

`例`<br>

```
class Sample
{
  public $message;
  public function __construnct(Message $message) {
    $this->message = $message;
  }
  public function run() { $this->message->send(); }
}

class Message
{
  public function send() { echo('メッセージ表示'); }
}
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

    $test = app()->make('lifeCycleTest');

    // サービスコンテナなしのパターン
    $message = new Message();
    $sample = new Sample($message);
    $sample->run();

    dd($test, app());
  }
}

class Sample
{
  public $message;

  public function __construct(Message $message)
  {
    $this->message = $message;
  }

  public function run()
  {
    $this->message->send();
  }
}

class Message
{
  public function send()
  {
    echo 'メッセージ表示';
  }
}
```

- https://localhost/servicecontainertest にアクセスしてみる<br>

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

    // サービスコンテナなしのパターン
    // $message = new Message();
    // $sample = new Sample($message);
    // $sample->run();

    // サービスコンテナapp()ありのパターン
    app()->bind('sample', Sample::class);
    $sample = app()->make('sample');
    $sample->run();

    dd($test, app());
  }
}

class Sample
{
  public $message;

  public function __construct(Message $message)
  {
    $this->message = $message;
  }

  public function run()
  {
    $this->message->send();
  }
}

class Message
{
  public function send()
  {
    echo 'メッセージ表示';
  }
}
```
