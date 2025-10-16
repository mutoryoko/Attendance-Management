# CoachTech 勤怠管理アプリ

## 概要

### 主な機能
本アプリはユーザーの勤怠と管理を目的とする勤怠管理アプリです。

#### 【一般ユーザー（スタッフ）側】
<ul>
	<li>会員登録・ログイン（メール認証対応）</li>
	<li>勤怠の打刻（出勤・退勤・休憩）</li>
	<li>勤怠時刻の修正申請</li>
	<li>勤怠一覧、申請一覧の確認</li>
</ul>

#### 【管理ユーザー側】
<ul>
	<li>全スタッフの勤怠一覧（日次・月次）の確認</li>
	<li>スタッフの勤怠情報詳細の確認、修正</li>
	<li>スタッフによる修正申請の承認</li>
	<li>スタッフ別勤怠一覧（月次）のCSV出力</li>
</ul>

### 実行環境
<ul>
	<li>Laravel Framework: 10.48.29</li>
	<li>PHP: 8.4</li>
	<li>mysql: 8.0.26</li>
	<li>nginx: 1.21.1</li>
	<li>mail: Mailhog</li>
</ul>

### URL
<ul>
	<li>開発環境: <a href="http://localhost">http://localhost</a> </li>
	<li>phpmyadmin: <a href="http://localhost:8080">http://localhost:8080</a> </li>
	<li>Mailhog: <a href="http://localhost:8025">http://localhost:8025</a></li>
</ul>

### ER図
<img src="ER.drawio.svg" width=70% />

&nbsp;

## Dockerビルド
```
git clone git@github.com:mutoryoko/Attendance-Management.git
docker compose up -d --build
```
&nbsp;
## 環境構築
```
docker compose exec php bash
composer install
cp .env.example .env
```
メール機能はMailhogを使用。<br />
.envファイルのMAIL_FROM_ADDRESSは任意のアドレスに変更可。

以下のコマンドを実行。
```
php artisan key:generate
php artisan migrate
php artisan db:seed
```
&nbsp;

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
.env.testingファイルを以下に変更。
```
APP_ENV=test
APP_KEY=

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
管理者1名、一般ユーザー3名が登録されている。<br />
未認証ユーザーはメール認証機能の確認に使用できる。
### 【管理者情報・AdminUsersTableSeeder】
| メールアドレス | パスワード |
| :---: | :---: |
| admin@seeder.com | admin-pass |

### 【一般ユーザー情報・UsersTableSeeder】
| ユーザー名 | メールアドレス | パスワード | メール認証の済否 |
| :---: | :---: | :---: | :---: |
| 鈴木一郎 | ichiro@seeder.com | password1 | 認証済 |
| 佐藤二郎 | jiro@seeder.com | password2 | 認証済 |
| 北島三郎 | saburo@seeder.com | password3 | 未認証 |

### 【勤怠情報】
* 直近30日分、出勤・退勤時間（ランダム）・休憩時間（1回または2回）で登録されている。
* 打刻できるようにするため、シーディングした当日の勤怠は登録されていない。

&nbsp;

## 本アプリに関する注意事項
* 一般ユーザーの打刻に関して、退勤ボタンを押さずに翌日となった場合、<br />
退勤時刻は空欄となり、出勤ボタンが押せるようになる。
* 勤怠一覧画面（月次）の詳細ボタンに関して、<br />
本アプリを使用する当日を含む過去の勤怠にのみ表示され、未来の勤怠には表示されない。
* 一般ユーザーによる勤怠情報修正の申請中は、管理者が承認するまで<br />
一般ユーザー、管理者共に再修正ができない。<br />
承認済みとなれば、再び同じ日の修正申請及び修正が可能となる。