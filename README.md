# 環境構築

1. Dockerを起動する

2. プロジェクト直下で、以下のコマンドを実行する

```
make init
```

## 開発環境
- 一般ユーザー会員登録画面：http://localhost/register
- 一般ユーザーログイン画面：http://localhost/login
- 管理者ユーザーログイン画面：http://localhost/admin/login
- phpMyAdmin：http://localhost:8080/

## メール認証
mailtrapというツールを使用しています。<br>
以下のリンクから会員登録をしてください。　<br>
https://mailtrap.io/

.envファイルのMAIL_MAILERからMAIL_ENCRYPTIONまでの項目をコピー＆ペーストしてください。　<br>
MAIL_FROM_ADDRESSは任意のメールアドレスを入力してください。　


## ER図
![表示](./test.drawio.svg)

## アカウント
name: 管理者ユーザー
email: admin@example.com  
password: adminpassword
-------------------------
name: 一般ユーザー
email: user1@example.com
password: password123
-------------------------

## PHPUnitを利用したテストに関して
以下のコマンド:
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database demo_test;

docker-compose exec php bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
```