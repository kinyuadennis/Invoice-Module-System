<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test API login returns token.
     */
    public function test_api_login_returns_token(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'owner_user_id' => null,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'token',
                'token_type',
                'expires_at',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'company_id',
                ],
            ],
        ]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertEquals('Bearer', $response->json('data.token_type'));
    }

    /**
     * Test API login fails with invalid credentials.
     */
    public function test_api_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test API login fails for user without company.
     */
    public function test_api_login_fails_for_user_without_company(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'company_id' => null,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status' => 'error',
            'message' => 'User account is not associated with a company.',
        ]);
    }

    /**
     * Test authenticated user can access protected endpoint.
     */
    public function test_authenticated_user_can_access_protected_endpoint(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        // Access protected endpoint
        $response = $this->getJson('/api/v1/auth/user', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
    }

    /**
     * Test unauthenticated user cannot access protected endpoint.
     */
    public function test_unauthenticated_user_cannot_access_protected_endpoint(): void
    {
        $response = $this->getJson('/api/v1/invoices');

        $response->assertStatus(401);
    }

    /**
     * Test logout revokes token.
     */
    public function test_logout_revokes_token(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        // Logout
        $logoutResponse = $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $logoutResponse->assertStatus(200);

        // Token should no longer work
        $response = $this->getJson('/api/v1/auth/user', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(401);
    }
}
