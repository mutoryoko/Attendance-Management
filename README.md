# CoachTech 勤怠管理アプリ

## 概要

#### 主な機能
本アプリはユーザーの勤怠と管理を目的とする勤怠管理アプリです。<br />
【一般ユーザー（スタッフ）側】
<ul>
	<li>会員登録・ログイン（メール認証対応）</li>
	<li>勤怠の打刻（出勤・退勤・休憩）</li>
	<li>勤怠時刻の修正申請</li>
	<li>勤怠一覧、申請一覧の確認</li>
</ul>
【管理ユーザー側】
<ul>
	<li>全スタッフの勤怠一覧</li>
	<li>スタッフの勤怠情報詳細の確認、修正</li>
	<li>修正申請の承認</li>
	<li>勤怠一覧情報のCSV出力</li>
</ul>

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
<img src="ER.drawio.svg" width=70% />

&nbsp;

## Dockerビルド
```
git clone git@github.com:mutoryoko/Attendance-Management.git
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
管理者1名、一般ユーザー3名分のダミーデータを用意しています。
### 【管理者情報】
| メールアドレス | パスワード |
| :---: | :---: |
| admin@seeder.com | admin-pass |

&nbsp;
### 【一般ユーザー情報】
| ユーザー名 | メールアドレス | パスワード | メール認証の済否 |
| :---: | :---: | :---: | :---: |
| 鈴木一郎 | ichiro@seeder.com | password1 | 認証済 |
| 佐藤二郎 | jiro@seeder.com | password2 | 認証済 |
| 北島三郎 | saburo@seeder.com | password3 | 未認証 |

一般ユーザーの勤怠情報は過去1ヶ月分、登録しています。<br />
打刻できるようにするため、シーディングした当日分は登録されていません。

&nbsp;

## 本アプリに関する注意事項
* 一般ユーザーの打刻に関して、退勤ボタンを押さずに翌日となった場合、<br />
退勤時刻は空欄となり、出勤ボタンが押せるようになります。
* 一般ユーザーの勤怠一覧画面（月次）の詳細ボタンに関して、<br />
本アプリを使用する当日の勤怠には表示されず、過去の勤怠にのみ表示されます。
* 管理者の勤怠一覧画面（日次）とスタッフ別勤怠一覧画面（月次）の詳細ボタンは、<br />
過去の勤怠と、本アプリを使用する当日の勤怠に表示されます。
* 一般ユーザーによる勤怠情報修正の申請中は、管理者が承認するまで<br />
一般ユーザー、管理者共に再修正ができません。<br />
承認済みとなれば、再び同じ日の修正申請及び修正が可能です。