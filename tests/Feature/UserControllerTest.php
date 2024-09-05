<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_check_phone_registration()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', '/api/user/check-phone', ['phone' => $user->phone]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'OTP send']);
    }

    public function user_can_register_with_valid_data()
    {
        $userData = User::factory()->make()->toArray();

        $response = $this->json('POST', '/api/user/register', $userData);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'registered successfully, and otp sent']);
    }

    public function user_cannot_register_with_invalid_data()
    {
        $userData = User::factory()->make(['phone' => null])->toArray();

        $response = $this->json('POST', '/api/user/register', $userData);

        $response->assertStatus(422);
    }

    public function user_can_login_with_valid_otp()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', '/api/user/login', ['phone' => $user->phone, 'otp' => '123456']);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'login successfully']);
    }

    public function user_cannot_login_with_invalid_otp()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', '/api/user/login', ['phone' => $user->phone, 'otp' => 'invalid']);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid OTP']);
    }

    public function user_can_generate_otp()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', '/api/user/generate-otp', ['phone' => $user->phone]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'OTP sent']);
    }

    public function user_cannot_generate_otp_with_invalid_phone()
    {
        $response = $this->json('POST', '/api/user/generate-otp', ['phone' => 'invalid']);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'User not found']);
    }
}
