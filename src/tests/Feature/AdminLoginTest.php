<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AdminUser;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    // メールアドレスのエラー
    public function test_show_error_when_email_is_missing(): void
    {
        AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin-pass'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    // パスワードのエラー
    public function test_show_error_when_password_is_missing(): void
    {
        AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin-pass'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    // 入力情報が間違っているエラー
    public function test_login_fails_with_invalid_credentials(): void
    {
        AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin-pass'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
