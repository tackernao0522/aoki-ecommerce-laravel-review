<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner; // eloquent エロクアント
use App\Models\Shop;
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

        $owners = Owner::select('id', 'name', 'email', 'created_at')
            ->paginate(3);

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
                'status' => 'info'
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
                'status' => 'info'
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

        return redirect()->back()->with([
            'message' => 'オーナー情報を削除しました。',
            'status' => 'alert'
        ]);
    }

    public function expiredOwnerIndex()
    {
        $expiredOwners = Owner::onlyTrashed()->get(); // 論理削除したデータを取得できる

        return view('admin.expired-owners', compact('expiredOwners'));
    }

    public function expiredOwnerDestroy($id)
    {
        Owner::onlyTrashed()->findOrFail($id)->forceDelete(); // 完全に削除する(物理的削除)

        return redirect()->back()->with([
            'message' => '期限切れオーナー情報を削除しました。',
            'status' => 'alert'
        ]);
    }
}
