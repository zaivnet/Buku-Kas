<?php

namespace Tests\Feature;

use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint04CategoryTest extends TestCase
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

    public function test_admin_can_view_categories_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.index', ['type' => 'income']));
        $response->assertStatus(200);
        $response->assertSee('Setoran Penjualan Outlet');
    }

    public function test_staff_cannot_access_categories_management(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.categories.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_custom_income_and_expense_category(): void
    {
        // Custom Income Category
        $responseIncome = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'      => 'Investasi Pemilik',
            'type'      => 'income',
            'is_active' => '1',
        ]);
        $responseIncome->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Investasi Pemilik', 'type' => 'income']);

        // Custom Expense Category
        $responseExpense = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'      => 'Biaya Listrik & Air',
            'type'      => 'expense',
            'is_active' => '1',
        ]);
        $responseExpense->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Biaya Listrik & Air', 'type' => 'expense']);
    }

    public function test_duplicate_category_name_in_same_type_is_rejected(): void
    {
        $existing = Category::where('type', TransactionTypeEnum::INCOME)->first();

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'      => $existing->name,
            'type'      => 'income',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_same_category_name_in_different_type_is_allowed(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'      => 'Bonus',
            'type'      => 'income',
            'is_active' => '1',
        ]);
        $response->assertRedirect();

        $response2 = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'      => 'Bonus',
            'type'      => 'expense',
            'is_active' => '1',
        ]);
        $response2->assertRedirect();

        $this->assertEquals(2, Category::where('name', 'Bonus')->count());
    }

    public function test_category_with_transactions_cannot_be_deleted(): void
    {
        $category = Category::first();
        $outlet = Outlet::first();

        Transaction::create([
            'type'        => $category->type,
            'category_id' => $category->id,
            'outlet_id'   => $outlet->id,
            'date'        => now()->toDateString(),
            'amount'      => 500000,
            'payer_name'  => 'Test Transaction',
            'created_by'  => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_active_and_of_type_scopes_work_correctly(): void
    {
        Category::create(['name' => 'Kategori Nonaktif', 'type' => 'income', 'is_active' => false]);

        $activeIncomeCategories = Category::active()->ofType(TransactionTypeEnum::INCOME)->get();

        foreach ($activeIncomeCategories as $cat) {
            $this->assertTrue($cat->is_active);
            $this->assertEquals(TransactionTypeEnum::INCOME, $cat->type);
        }

        $this->assertFalse($activeIncomeCategories->contains('name', 'Kategori Nonaktif'));
    }
}
