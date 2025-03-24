<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    /**
     * A basic unit test example.
     */


    // 名前が未入力の場合、バリデーションメッセージが表示されるか
    public function test_name_is_required()
    {
        $this->withoutMiddleware();

        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors('name');
    }

    // メールアドレスが未入力の場合、バリデーションメッセージが表示されるか
    public function test_email_is_required()
    {
        $this->withoutMiddleware();

        $response = $this->post('/register', [
            'name' => 'test user',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors('email');
    }

    // パスワードが8文字未満の場合、バリデーションメッセージが表示されるか
    public function test_password_minimum_length()
    {
        $this->withoutMiddleware();

        $response = $this->post('/register', [
            'name' => 'test user',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);
        $response->assertSessionHasErrors('password');
    }

    // パスワードが一致しない場合、バリデーションメッセージが表示されるか
    public function test_passwords_do_not_match()
    {
        $this->withoutMiddleware();

        $response = $this->post('/register', [
            'name' => 'test user',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password321',
        ]);
        $response->assertSessionHasErrors('password');
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示されるか
    public function test_password_is_required()
    {
        $this->withoutMiddleware();

        $response = $this->post('/register', [
            'name' => 'test user',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $response->assertSessionHasErrors('password');
    }

    // フォームに内容が入力されていた場合、データが聖女に保存されるか
    public function test_user_registration_saved_data()
    {
        $this->withoutMiddleware();

        $response = $this->post('/register', [
            'name' => 'test user',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'test user',
            'email' => 'test@example.com',
        ]);
    }

}
