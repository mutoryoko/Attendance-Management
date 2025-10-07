<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8" />
        <title>メール認証のご案内</title>
    </head>

    <body>
        <p>{{ $user->name }} 様</p>
        <p>Coachtech 勤怠管理アプリにご登録いただき、ありがとうございます。</p>
        <p>下のボタンをクリックしてメールアドレスを認証してください。</p>
        <p>
            <a href="{{ $verificationUrl }}" style="display:inline-block; padding:10px 20px; background:#3f9be6; color:#fff; text-decoration:none; border-radius:5px;">
                メールアドレスを認証する
            </a>
        </p>
        <p>もし心当たりがない場合はこのメールを無視してください。</p>
    </body>
</html>