## 54 Store 保存の解説

### CRUD (Store)

Form タグ、method="post" action="store 指定"<br>
@csrf 必須<br>

戻るボタンは type="button"をつけておく<br>

input タグ name="" 属性を<br>
Request \$request インスタンスで取得<br>
dd(\$request->name);<br>

### CRUD (Store)バリデーション 1

View<br>
バリデーションで画面読み込み後も入力した値を保持したい場合<br>

`<input name="email" value="{{ old('email') }}">`<br>

### CRUD (Store)バリデーション 2

Model<br>

$fillableか$guarded で設定<br>

```
protected $fillable = [
  'name',
  'email',
  'password',
];
```

### CRUD (Store)バリデーション 3

Controller
簡易バリデーション or カスタムリクエスト<br>

```
$request->validate([
  'name' => 'required|string|max:255',
  'email' => 'required|string|email|max:255|unique:owners',
  'Password' => 'required|string|confirmed|min:8',
]);
```

### CRUD (Store)バリデーション 4

Controller<br>
保存処理<br>

```
Owner::create([
  'name' => $request->name,
  'email' => $request->email,
  'password' => Hash::make($request->password),
]);

return redirect()->route('admin.owners.index);
```

## 55 保存(簡易バリデーション)

- `resources/views/admin/owners/create.blade.php`を編集<br>

```html:create.blade.php
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      オーナー登録
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <section class="text-gray-600 body-font relative">
            <div class="container px-5 mx-auto">
              <div class="flex flex-col text-center w-full mb-12">
                <h1
                  class="sm:text-3xl text-2xl font-medium title-font mb-4 text-gray-900"
                >
                  オーナー登録
                </h1>
              </div>
              <div class="lg:w-1/2 md:w-2/3 mx-auto">
                <!-- Validation Errors -->
                <x-auth-validation-errors class="mb-4" :errors="$errors" />
                <form method="POST" action="{{ route('admin.owners.store') }}">
                  @csrf
                  <div class="-m-2">
                    <div class="p-2 w-1/2 mx-auto">
                      <div class="relative">
                        <label
                          for="name"
                          class="leading-7 text-sm text-gray-600"
                        >
                          オーナー名
                        </label>
                        <input
                          type="text"
                          id="name"
                          name="name"
                          class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          value="{{ old('name') }}"
                          required
                        />
                      </div>
                    </div>
                    <div class="p-2 w-1/2 mx-auto">
                      <div class="relative">
                        <label
                          for="email"
                          class="leading-7 text-sm text-gray-600"
                        >
                          メールアドレス
                        </label>
                        <input
                          type="email"
                          id="email"
                          name="email"
                          class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          value="{{ old('email') }}"
                          required
                        />
                      </div>
                    </div>
                    <div class="p-2 w-1/2 mx-auto">
                      <div class="relative">
                        <label
                          for="password"
                          class="leading-7 text-sm text-gray-600"
                        >
                          パスワード
                        </label>
                        <input
                          type="password"
                          id="password"
                          name="password"
                          class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          required
                        />
                      </div>
                    </div>
                    <div class="p-2 w-1/2 mx-auto">
                      <div class="relative">
                        <label
                          for="password_confirmation"
                          class="leading-7 text-sm text-gray-600"
                        >
                          パスワード確認
                        </label>
                        <input
                          type="password"
                          id="password_confirmation"
                          name="password_confirmation"
                          class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          required
                        />
                      </div>
                    </div>
                    <div class="p-2 w-full flex justify-around mt-4">
                      <button
                        type="button"
                        onclick="location.href='{{ route('admin.owners.index') }}'"
                        class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-gray-400 rounded text-lg"
                      >
                        戻る
                      </button>
                      <button
                        type="submit"
                        class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg"
                      >
                        登録する
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
```

- `app/Http/Controllers/Admin/OwnersController.php`を編集<br>

```php:OwnersController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner; // eloquent エロクアント
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // QueryBuilder クエリビルダ
use Illuminate\Support\Facades\Hash;

class OwnersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:admin');
  }

  public function index()
  {
    // $date_now = Carbon::now();
    // $date_parse = Carbon::parse(now());
    // echo $date_now . '<br>';
    // echo $date_now->year . '<br>';
    // echo $date_parse . '<br>';

    // $e_all = Owner::all();
    // $q_get = DB::table('owners')->select('name', 'created_at')->get();
    // $q_first = DB::table('owners')->select('name')->first();

    // $c_test = collect([
    //     'name' => 'テスト',
    // ]);

    // var_dump($q_first);

    // dd($e_all, $q_get, $q_first, $c_test);

    $owners = Owner::select('name', 'email', 'created_at')->get();

    return view('admin.owners.index', compact('owners'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('admin.owners.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  // 編集
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:owners',
      'password' => 'required|string|confirmed|min:8',
    ]);

    Owner::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);

    return redirect()->route('admin.owners.index');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }
}
```

## 56 フラッシュメッセージ

### Store フラッシュメッセージ 1

英語だと toaster<br>
Session を使って１度だけ表示<br>

Controller 側<br>

```
session()->flash('message', '登録できました。);
Session::flash('message', '');
redirect()->with('message', '');
```

数秒後に消したい場合は JS も必要

### Store フラッシュメッセージ 2

View 側(コンポーネント)<br>

```
@props(['status' => 'info'])

@php
if($status === 'info'){ $bgColor = 'bg-blue-300'; }
if($status === 'error'){ $bgColor = 'bg-red-500'; }
@endphp

@if(session('message'))
  <div class="{{ $bgColor }} w-1/2 mx-auto p-2 text-white">
    {{ session('message') }}
  </div>
@endif
```

View 側<br>

```
<x-flash-message status="unfo" />
```

### ハンズオン

- `app/Http/Controllers/Admin/OwnersController.php`を編集<br>

```php:OwnersController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner; // eloquent エロクアント
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // QueryBuilder クエリビルダ
use Illuminate\Support\Facades\Hash;

class OwnersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:admin');
  }

  public function index()
  {
    // $date_now = Carbon::now();
    // $date_parse = Carbon::parse(now());
    // echo $date_now . '<br>';
    // echo $date_now->year . '<br>';
    // echo $date_parse . '<br>';

    // $e_all = Owner::all();
    // $q_get = DB::table('owners')->select('name', 'created_at')->get();
    // $q_first = DB::table('owners')->select('name')->first();

    // $c_test = collect([
    //     'name' => 'テスト',
    // ]);

    // var_dump($q_first);

    // dd($e_all, $q_get, $q_first, $c_test);

    $owners = Owner::select('name', 'email', 'created_at')->get();

    return view('admin.owners.index', compact('owners'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('admin.owners.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:owners',
      'password' => 'required|string|confirmed|min:8',
    ]);

    Owner::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);

    return redirect()
      ->route('admin.owners.index')
      ->with('message', 'オーナー登録を実施しました。'); // 編集
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }
}
```

- `resources/views/admin/owners/index.blade.php`を編集<br>

```html:index.blade.php
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      オーナー一覧
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <section class="text-gray-600 body-font">
            <div class="container px-5 mx-auto">
              <div class="lg:w-2/3 w-full mx-auto overflow-auto">
                <!-- 追加 -->
                <x-flash-message status="info" />
                <div class="flex justify-end mb-4">
                  <button
                    onclick="location.href='{{ route('admin.owners.create') }}'"
                    class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg"
                  >
                    新規登録する
                  </button>
                </div>
                <table class="table-auto w-full text-left whitespace-no-wrap">
                  <thead>
                    <tr>
                      <th
                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl"
                      >
                        名前
                      </th>
                      <th
                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                      >
                        メールアドレス
                      </th>
                      <th
                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                      >
                        作成日
                      </th>
                      <th
                        class="w-10 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tr rounded-br"
                      ></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($owners as $owner)
                    <tr>
                      <td class="px-4 py-3">{{ $owner->name }}</td>
                      <td class="px-4 py-3">{{ $owner->email }}</td>
                      <td class="px-4 py-3">
                        {{ $owner->created_at->diffForHumans() }}
                      </td>
                      <td class="w-10 text-center">
                        <input name="plan" type="radio" />
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </section>

          {{-- エロクアント @foreach ($e_all as $e_owner) {{ $e_owner->name }}
          {{ $e_owner->created_at->diffForHumans() }} @endforeach
          <br />
          クエリビルダ @foreach ($q_get as $q_owner) {{ $q_owner->name }} {{
          Carbon\Carbon::parse($q_owner->created_at)->diffForHumans() }}
          @endforeach --}}
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
```

- `resources/views/components/flash-message.blade.php`ファイルを作成<br>

```html:flash-message.blade.php
@props(['status' => 'info']) @php if($status === 'info'){ $bgColor =
'bg-blue-300'; } if($status === 'error'){ $bgColor = 'bg-red-500'; } @endphp @if
(session('message'))
<div class="{{ $bgColor }} w-1/2 mx-auto p-2 text-white">
  {{ session('message') }}
</div>
@endif
```
