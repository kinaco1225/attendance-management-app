# 勤怠アプリ

## アプリ概要
本アプリは、従業員の勤怠管理を行うためのWebアプリケーションです。
出勤・退勤・休憩の打刻機能に加え、勤怠修正申請や管理者による承認機能を実装しています。

## 主な機能
- ユーザー登録 / ログイン
- 出勤 / 退勤打刻
- 休憩開始 / 終了
- 勤怠一覧表示
- 勤怠詳細表示
- 勤怠修正申請
- 管理者による申請承認
- メール認証

## 環境構築
### Docker のビルドからマイグレーション、シーディングまでを行い開発環境を構築
- `docker compose up -d --build` コンテナが作成
- `docker compose exec php bash` PHPコンテナ内にログイン
- `composer install` 依存パッケージをインストール
- `cp .env.example .env` ファイルをコピー(.env作成)
- `.env` の設定変更
- `php artisan key:generate`  アプリキー生成
- `php artisan migrate --seed` によりデータベースをセットアップ
---
## .env 設定について（メール送信（MailHog））
#### 本アプリでは、開発環境に MailHog を使用しています。以下のように .envに設定してください。
- MAIL_FROM_ADDRESS=`nullno-reply@example.com`
#### MailHog Web UI：
- `http://localhost:8025`
- -- 
## テスト用ユーザー情報
#### 本アプリには 動作確認用のテストユーザーを3名、管理者を１名用意しています。いずれも 同一のパスワードでログイン可能です。
#### テストユーザーの勤怠状況は、seedした日から一か月前まで作成されてます。
##### テストユーザー①
- login-email `user1@example.com`
- login-password `password`
##### テストユーザー②
- login-email `user2@example.com`
- login-password `password`
##### テストユーザー3
- login-email `user3@example.com`
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

## データベース設計（ER図）
<img width="641" height="571" alt="er_diagram" src="https://github.com/user-attachments/assets/06a68869-a99a-4077-8a9e-191d555a6bbb" />

## URL
- 新規会員登録  
`http://localhost/register`

- ログイン  
`http://localhost/login`

- 管理者ログイン  
`http://localhost/admin/login`
