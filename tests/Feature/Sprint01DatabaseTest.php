<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint01DatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_outlets_categories_and_users(): void
    {
        $this->seed();

        $this->assertDatabaseHas('outlets', ['name' => 'Outlet 1']);
        $this->assertDatabaseHas('outlets', ['name' => 'Outlet 2']);

        $this->assertDatabaseHas('categories', ['name' => 'Setoran Penjualan Outlet', 'type' => 'income']);
        $this->assertDatabaseHas('categories', ['name' => 'Belanja Stok', 'type' => 'expense']);

        $this->assertDatabaseHas('users', ['email' => 'admin@bukukas.local', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'staff1@bukukas.local', 'role' => 'staff']);
    }

    public function test_transaction_for_user_scope_filters_by_staff_outlet(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@bukukas.local')->first();
        $staff = User::where('email', 'staff1@bukukas.local')->first();
        $outlet1 = Outlet::where('name', 'Outlet 1')->first();
        $outlet2 = Outlet::where('name', 'Outlet 2')->first();
        $catIncome = Category::where('type', TransactionTypeEnum::INCOME)->first();

        // Transaksi di Outlet 1
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $catIncome->id,
            'outlet_id'   => $outlet1->id,
            'date'        => now()->toDateString(),
            'amount'      => 1500000,
            'payer_name'  => 'Setoran Outlet 1',
            'created_by'  => $admin->id,
        ]);

        // Transaksi di Outlet 2
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $catIncome->id,
            'outlet_id'   => $outlet2->id,
            'date'        => now()->toDateString(),
            'amount'      => 2000000,
            'payer_name'  => 'Setoran Outlet 2',
            'created_by'  => $admin->id,
        ]);

        // Admin harus bisa lihat semua transaksi (2)
        $this->assertEquals(2, Transaction::forUser($admin)->count());

        // Staff Outlet 1 hanya bisa lihat transaksi Outlet 1 (1)
        $this->assertEquals(1, Transaction::forUser($staff)->count());
        $this->assertEquals($outlet1->id, Transaction::forUser($staff)->first()->outlet_id);
    }
}
