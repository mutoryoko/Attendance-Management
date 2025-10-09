<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    //　名前のエラー
    public function test_show_error_when_name_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');

        $this->get(route('register'))->assertSee('お名前を入力してください');
    }

    //　メールアドレスのエラー
    public function test_show_error_when_email_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => 'test-user',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);

        $this->get(route('register'))->assertSee('メールアドレスを入力してください');
    }

    //　パスワードが8文字未満でエラー
    public function test_show_error_when_password_is_too_short(): void
    {
        $response = $this->post('/register', [
            'name' => 'test-user',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors(['password']);

        $this->get(route('register'))->assertSee('パスワードは8文字以上で入力してください');
    }

    //　パスワードが一致しないエラー
    public function test_show_error_when_password_is_wrong(): void
    {
        $response = $this->post('/register', [
            'name' => 'test-user',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['password']);

        $this->get(route('register'))->assertSee('パスワードと一致しません');
    }

    //　パスワード未入力のエラー
    public function test_show_error_when_password_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => 'test-user',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['password']);

        $this->get(route('register'))->assertSee('パスワードを入力してください');
    }

    //　会員登録
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'test-user',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        // メール機能ありのため、メール認証誘導画面へ変更
        $response->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}