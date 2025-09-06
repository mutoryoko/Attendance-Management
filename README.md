# Attendance-Management

## 概要

#### 主な機能

#### 実行環境
<ul>
	<li>PHP: 8.4</li>
	<li>mysql: 8.0.26</li>
	<li>nginx:1.21.1</li>
	<li>Laravel Framework: 10.48.29</li>
</ul>

#### URL
<ul>
	<li>開発環境: <a href="http://localhost">http://localhost</a> </li>
	<li>phpmyadmin: <a href="http://localhost:8080">http://localhost:8080</a> </li>
</ul>

#### ER図

&nbsp;

## Dockerビルド
```
git clone git@github.com:mutoryoko/FleaMarket.git
docker compose up -d --build
```
&nbsp;
## Laravel環境構築
```
docker compose exec php bash
composer install
cp .env.example .env
```
メール機能はMailtrapを想定。<br />
アカウント作成後、下記を参考に.envファイルを編集する。
```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=587または2525
MAIL_USERNAME=Mailtrapのユーザー名
MAIL_PASSWORD=Mailtrapのパスワード
MAIL_ENCRYPTION=tls　（Laravel9〜は省略可）
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```
&nbsp;

.envファイルを編集後、以下のコマンドを実行。
```
php artisan key:generate
php artisan migrate
php artisan db:seed
```

## テスト環境構築
```
docker compose exec mysql bash
mysql -u root -p
```
パスワード:rootを入力してMySQLコンテナ内に入る。
```
CREATE DATABASE demo_test;
```
データベースができたら、MySQLコンテナを抜ける。
```
docker compose exec php bash
cp .env .env.testing
```
.env.testingファイルのAPP_ENV、APP_KEYを以下に変更。
```
APP_ENV=test
APP_KEY=
```
.env.testingファイルのデータベース情報を以下に変更。
```
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root
```
.env.testingを編集後、以下のコマンドを実行。
```
php artisan key:generate --env=testing
php artisan config:clear
php artisan migrate --env=testing
```
テスト実行。
```
php artisan test
```
&nbsp;

## Seederファイルについて


## 本アプリに関する注意事項