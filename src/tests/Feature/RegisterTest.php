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
