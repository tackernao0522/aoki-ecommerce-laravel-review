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
