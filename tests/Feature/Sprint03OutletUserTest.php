<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Sprint03OutletUserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@bukukas.local')->first();
        $this->staff = User::where('email', 'staff1@bukukas.local')->first();
    }

    public function test_admin_can_view_outlets_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.outlets.index'));
        $response->assertStatus(200);
        $response->assertSee('Outlet 1');
    }

    public function test_admin_can_create_new_outlet(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.outlets.store'), [
            'name'      => 'Outlet 3 - Sleman',
            'address'   => 'Jl. Magelang Km 12',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.outlets.index'));
        $this->assertDatabaseHas('outlets', [
            'name' => 'Outlet 3 - Sleman',
        ]);
    }

    public function test_admin_can_update_outlet(): void
    {
        $outlet = Outlet::first();

        $response = $this->actingAs($this->admin)->put(route('admin.outlets.update', $outlet), [
            'name'      => 'Outlet 1 - Updated',
            'address'   => 'Alamat Baru',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.outlets.index'));
        $this->assertDatabaseHas('outlets', [
            'id'   => $outlet->id,
            'name' => 'Outlet 1 - Updated',
        ]);
    }

    public function test_admin_can_toggle_outlet_status(): void
    {
        $outlet = Outlet::first();
        $initialStatus = $outlet->is_active;

        $response = $this->actingAs($this->admin)->patch(route('admin.outlets.toggle-status', $outlet));

        $response->assertRedirect();
        $this->assertEquals(!$initialStatus, $outlet->fresh()->is_active);
    }

    public function test_outlet_with_transactions_cannot_be_deleted(): void
    {
        $outlet = Outlet::first();
        $category = Category::first();

        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $category->id,
            'outlet_id'   => $outlet->id,
            'date'        => now()->toDateString(),
            'amount'      => 100000,
            'payer_name'  => 'Test Payer',
            'created_by'  => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.outlets.destroy', $outlet));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('outlets', ['id' => $outlet->id, 'deleted_at' => null]);
    }

    public function test_admin_can_create_staff_user_with_active_outlet(): void
    {
        $outlet = Outlet::where('is_active', true)->first();

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'                  => 'Staff Baru',
            'email'                 => 'staffbaru@bukukas.local',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'staff',
            'outlet_id'             => $outlet->id,
            'is_active'             => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'staffbaru@bukukas.local')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertEquals(RoleEnum::STAFF, $user->role);
        $this->assertEquals($outlet->id, $user->outlet_id);
    }

    public function test_staff_user_creation_fails_when_assigning_inactive_outlet(): void
    {
        $inactiveOutlet = Outlet::create([
            'name'      => 'Outlet Nonaktif',
            'address'   => 'Alamat Test',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'                  => 'Staff Test',
            'email'                 => 'staffinvalid@bukukas.local',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'staff',
            'outlet_id'             => $inactiveOutlet->id,
            'is_active'             => '1',
        ]);

        $response->assertSessionHasErrors('outlet_id');
        $this->assertDatabaseMissing('users', ['email' => 'staffinvalid@bukukas.local']);
    }

    public function test_admin_cannot_deactivate_or_delete_self(): void
    {
        // Test deactivate self
        $responseDeactivate = $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $this->admin));
        $responseDeactivate->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->is_active);

        // Test delete self
        $responseDelete = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        $responseDelete->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
