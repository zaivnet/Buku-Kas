<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint02AuthRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_route_is_disabled(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(404);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@bukukas.local')->first();

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/outlets');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/categories');
        $response->assertStatus(200);
    }

    public function test_staff_cannot_access_admin_routes_and_receives_403(): void
    {
        $this->seed();

        $staff = User::where('email', 'staff1@bukukas.local')->first();

        $response = $this->actingAs($staff)->get('/admin/users');
        $response->assertStatus(403);

        $response = $this->actingAs($staff)->get('/admin/outlets');
        $response->assertStatus(403);

        $response = $this->actingAs($staff)->get('/admin/categories');
        $response->assertStatus(403);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email'     => 'inactive@bukukas.local',
            'password'  => 'password123',
            'role'      => RoleEnum::STAFF,
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email'    => 'inactive@bukukas.local',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_active_user_can_login_and_redirects_to_dashboard(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email'    => 'admin@bukukas.local',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }
}
