## 60 ソフトデリート 処理

- `database/migrations/create_owners_table.php`を編集<br>

```php:create_owners_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('owners', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->rememberToken();
      $table->timestamps();
      // 追加
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('owners');
  }
}
```

- `app/Models/Owner.php`を編集<br>

```php:Owner.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 追加

class Owner extends Authenticatable
{
  use HasFactory, SoftDeletes; // 編集

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = ['name', 'email', 'password'];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ['password', 'remember_token'];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];
}
```

- `$ php artisan migrate:refresh --seed`を実行<br>

* `app/Http/Controllers/Admin/OwnersController.php`を編集<br>

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

    $owners = Owner::select('id', 'name', 'email', 'created_at')->get();

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
      ->with('message', 'オーナー登録を実施しました。');
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
    $owner = Owner::findOrFail($id);

    return view('admin.owners.edit', compact('owner'));
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
    $owner = Owner::findOrFail($id);

    // $request->validate([
    //     'name' => 'required|string|max:255',
    //     'email' => 'required|string|email|max:255|unique:owners',
    //     'password' => 'required|string|confirmed|min:8',
    // ]);

    $owner->name = $request->name;
    $owner->email = $request->email;
    $owner->password = Hash::make($request->password);

    $owner->save();

    return redirect()
      ->route('admin.owners.index')
      ->with('message', 'オーナ情報を更新しました。');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  // 編集
  public function destroy($id)
  {
    Owner::findOrFail($id)->delete(); // ソフトデリート

    return redirect()
      ->back()
      ->with([
        'message' => 'オーナー情報を削除しました。',
        'status' => 'alert',
      ]);
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
                <!-- 編集 -->
                <x-flash-message status="session('status')" />
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
                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tr rounded-br"
                      ></th>
                      <th
                        class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tr rounded-br"
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
                      <td class="px-4 py-3">
                        <button
                          type="button"
                          onclick="location.href='{{ route('admin.owners.edit', $owner->id) }}'"
                          class="text-white bg-indigo-400 border-0 py-2 px-4 focus:outline-none hover:bg-indigo-500 rounded"
                        >
                          編集
                        </button>
                      </td>
                      <form
                        id="delete_{{ $owner->id }}"
                        action="{{ route('admin.owners.destroy', $owner->id) }}"
                        method="POST"
                      >
                        @csrf @method('DELETE')
                        <td class="px-4 py-3">
                          <a
                            href="#"
                            data-id="{{ $owner->id }}"
                            onclick="deletePost(this)"
                            type="button"
                            class="text-white bg-red-400 border-0 py-2 px-4 focus:outline-none hover:bg-red-500 rounded"
                          >
                            削除
                          </a>
                        </td>
                      </form>
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
  <script>
    function deletePost(e) {
      'use strict'
      if (confirm('本当に削除してもいいですか？')) {
        document.getElementById('delete_' + e.dataset.id).submit()
      }
    }
  </script>
</x-app-layout>
```

- `resources/views/components/flash-message.blade.php`を編集<br>

```php:flash-message.blade.php
@props(['status' => 'info'])

@php
// 編集
if(session('status') === 'info'){ $bgColor = 'bg-blue-300'; }
if(session('status') === 'alert'){ $bgColor = 'bg-red-500'; }
// ここまで
@endphp

@if (session('message'))
<div class="{{ $bgColor }} w-1/2 mx-auto p-2 text-white">
  {{ session('message') }}
</div>
@endif
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

    $owners = Owner::select('id', 'name', 'email', 'created_at')->get();

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
      ->with([
        // 編集
        'message' => 'オーナー登録を実施しました。',
        'status' => 'info',
      ]);
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
    $owner = Owner::findOrFail($id);

    return view('admin.owners.edit', compact('owner'));
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
    $owner = Owner::findOrFail($id);

    // $request->validate([
    //     'name' => 'required|string|max:255',
    //     'email' => 'required|string|email|max:255|unique:owners',
    //     'password' => 'required|string|confirmed|min:8',
    // ]);

    $owner->name = $request->name;
    $owner->email = $request->email;
    $owner->password = Hash::make($request->password);

    $owner->save();

    return redirect()
      ->route('admin.owners.index')
      ->with([
        // 編集
        'message' => 'オーナ情報を更新しました。',
        'status' => 'info',
      ]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    Owner::findOrFail($id)->delete(); // ソフトデリート

    return redirect()
      ->back()
      ->with([
        'message' => 'オーナー情報を削除しました。',
        'status' => 'alert',
      ]);
  }
}
```
