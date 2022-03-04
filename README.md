# Section1: 紹介

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
