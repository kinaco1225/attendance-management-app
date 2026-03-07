# 勤怠アプリ

## 環境構築
### Docker のビルドからマイグレーション、シーディングまでを行い開発環境を構築
- `docker-compose up -d --build` コンテナが作成
- `docker-compose exec php bash` PHPコンテナ内にログイン
- `composer install` をインストール
- `cp .env.example .env` ファイルをコピー(.env作成)
- `.env` の設定変更
- `php artisan key:generate`  アプリキー生成
- `php artisan migrate --seed` によりデータベースをセットアップ
---
## .env 設定について（メール送信（MailHog））
#### 本アプリでは、開発環境に MailHog を使用しています。以下のように .envに設定してください。
- MAIL_FROM_ADDRESS=`ullno-reply@example.com`
#### MailHog Web UI：
- `http://localhost:8025`
- -- 
## テスト用ユーザー情報
#### 本アプリには 動作確認用のテストユーザーを3名、管理者を１名用意しています。いずれも 同一のパスワードでログイン可能です。
##### テストユーザー①
- login-email `user1@example.com`
- login-password `password`
##### テストユーザー②
- login-email `user2@example.com`
- login-password `password`
##### テストユーザー3
- login-email `user2@example.com`
- login-password `password`
##### 管理者
- login-email `admin@example.com`
- login-password `password`
- -- 
## テスト実行方法
#### 本アプリでは Feature テストを用意しています。
- `php artisan test --testsuite=Feature`テスト実行
---
## 使用技術（実行環境）
- PHP 8.x
- Laravel 8.x
- MySQL 8.x
- WSL2 + Docker（開発環境）
--- 

## ER図
[er_diagram.drawio](https://github.com/user-attachments/files/25814271/er_diagram.drawio)

## URL
- 新規会員登録: http://localhost/register
- ログイン: http://localhost/login
- 管理者ログイン: http://localhost/admin/login
