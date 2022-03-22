## 68 Shop 作成 その 2

https://readouble.com/laravel/8.x/ja/database.html (データベーストランザクション)<br>

- `app/Http/Controllers/Admin/OwnersController.php`を編集<br>

```php:OwnersController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner; // eloquent エロクアント
use App\Models\Shop; // 追記
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // QueryBuilder クエリビルダ
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    $owners = Owner::select('id', 'name', 'email', 'created_at')->paginate(3);

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

    try {
      DB::transaction(function () use ($request) {
        $owner = Owner::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => Hash::make($request->password),
        ]);

        Shop::create([
          'owner_id' => $owner->id,
          'name' => '店名を入力してください',
          'information' => '',
          'filename' => '',
          'is_selling' => true,
        ]);
      }, 2);
    } catch (Throwable $e) {
      Log::error($e);
      throw $e;
    }

    return redirect()
      ->route('admin.owners.index')
      ->with([
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

  public function expiredOwnerIndex()
  {
    $expiredOwners = Owner::onlyTrashed()->get(); // 論理削除したデータを取得できる

    return view('admin.expired-owners', compact('expiredOwners'));
  }

  public function expiredOwnerDestroy($id)
  {
    Owner::onlyTrashed()
      ->findOrFail($id)
      ->forceDelete(); // 完全に削除する(物理的削除)

    return redirect()
      ->back()
      ->with([
        'message' => '期限切れオーナー情報を削除しました。',
        'status' => 'alert',
      ]);
  }
}
```

- 新規オーナー登録してみる<br>

* `resources/views/admin/owners/edit.blade.php`を編集<br>

```php:edit.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            オーナー情報編集
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <section class="text-gray-600 body-font relative">
                        <div class="container px-5 mx-auto">
                            <div class="flex flex-col text-center w-full mb-12">
                                <h1 class="sm:text-3xl text-2xl font-medium title-font mb-4 text-gray-900">オーナー情報編集</h1>
                            </div>
                            <div class="lg:w-1/2 md:w-2/3 mx-auto">
                                <!-- Validation Errors -->
                                <x-auth-validation-errors class="mb-4" :errors="$errors" />
                                <form method="post" action="{{ route('admin.owners.update', $owner->id) }}">
                                    @method('PUT')
                                    @csrf
                                    <div class="-m-2">
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="name" class="leading-7 text-sm text-gray-600">オーナー名</label>
                                                <input type="text" id="name" name="name"
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                                    value="{{ old('name', $owner->name) }}" required />
                                            </div>
                                        </div>
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="email"
                                                    class="leading-7 text-sm text-gray-600">メールアドレス</label>
                                                <input type="email" id="email" name="email"
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                                    value="{{ old('email', $owner->email) }}" required />
                                            </div>
                                        </div>
                                        // 追加
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="shop" class="leading-7 text-sm text-gray-600">店名</label>
                                                <div
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ $owner->shop->name }}</div>
                                            </div>
                                        </div>
                                        // ここまで
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="password"
                                                    class="leading-7 text-sm text-gray-600">パスワード</label>
                                                <input type="password" id="password" name="password"
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                                    required />
                                            </div>
                                        </div>
                                        <div class="p-2 w-1/2 mx-auto">
                                            <div class="relative">
                                                <label for="password_confirmation"
                                                    class="leading-7 text-sm text-gray-600">パスワード確認</label>
                                                <input type="password" id="password_confirmation"
                                                    name="password_confirmation"
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-purple-500 focus:bg-white focus:ring-2 focus:ring-purple-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                                    required />
                                            </div>
                                        </div>
                                        <div class="p-2 w-full flex justify-around mt-4">
                                            <button type="button"
                                                onclick="location.href='{{ route('admin.owners.index') }}'"
                                                class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-gray-400 rounded text-lg">戻る</button>
                                            <button type="submit"
                                                class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">更新する</button>
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