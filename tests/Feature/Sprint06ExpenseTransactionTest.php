<?php

namespace Tests\Feature;

use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Sprint06ExpenseTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff1;
    private User $staff2;
    private Outlet $outlet1;
    private Outlet $outlet2;
    private Category $catExpense;
    private Category $catIncome;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@bukukas.local')->first();
        $this->staff1 = User::where('email', 'staff1@bukukas.local')->first();

        $this->outlet1 = Outlet::where('name', 'Outlet 1')->first();
        $this->outlet2 = Outlet::where('name', 'Outlet 2')->first();

        $this->staff2 = User::factory()->create([
            'name'      => 'Staff Outlet 2',
            'email'     => 'staff2@bukukas.local',
            'role'      => 'staff',
            'outlet_id' => $this->outlet2->id,
            'is_active' => true,
        ]);

        $this->catExpense = Category::where('type', TransactionTypeEnum::EXPENSE)->first();
        $this->catIncome = Category::where('type', TransactionTypeEnum::INCOME)->first();
    }

    public function test_staff_can_view_expense_transactions_for_their_outlet_only(): void
    {
        // Transaksi Pengeluaran di Outlet 1
        Transaction::create([
            'type'        => TransactionTypeEnum::EXPENSE,
            'category_id' => $this->catExpense->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => now()->toDateString(),
            'amount'      => 450000,
            'payer_name'  => 'Belanja Stok Toko 1',
            'created_by'  => $this->staff1->id,
        ]);

        // Transaksi Pengeluaran di Outlet 2
        Transaction::create([
            'type'        => TransactionTypeEnum::EXPENSE,
            'category_id' => $this->catExpense->id,
            'outlet_id'   => $this->outlet2->id,
            'date'        => now()->toDateString(),
            'amount'      => 800000,
            'payer_name'  => 'Belanja Stok Toko 2',
            'created_by'  => $this->staff2->id,
        ]);

        $response = $this->actingAs($this->staff1)->get(route('transactions.expense'));
        $response->assertStatus(200);
        $response->assertSee('Belanja Stok Toko 1');
        $response->assertDontSee('Belanja Stok Toko 2');
    }

    public function test_staff_outlet_is_forced_for_expense_transactions(): void
    {
        $response = $this->actingAs($this->staff1)->post(route('transactions.store'), [
            'type'        => 'expense',
            'date'        => now()->toDateString(),
            'category_id' => $this->catExpense->id,
            'outlet_id'   => $this->outlet2->id, // Manipulasi request ke Outlet 2
            'amount'      => 750000,
            'payer_name'  => 'Beli Bahan Baku',
        ]);

        $response->assertRedirect(route('transactions.expense'));

        $this->assertDatabaseHas('transactions', [
            'payer_name' => 'Beli Bahan Baku',
            'type'       => 'expense',
            'outlet_id'  => $this->outlet1->id, // Dipaksa ke Outlet 1 milik Staff 1
        ]);
    }

    public function test_expense_transaction_with_proof_image_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('nota_pembelian.jpg', 1800, 1800);

        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'type'        => 'expense',
            'date'        => now()->toDateString(),
            'category_id' => $this->catExpense->id,
            'outlet_id'   => $this->outlet1->id,
            'amount'      => 1200000,
            'payer_name'  => 'Gaji Karyawan Toko 1',
            'proof_image' => $file,
        ]);

        $response->assertRedirect(route('transactions.expense'));

        $trx = Transaction::where('payer_name', 'Gaji Karyawan Toko 1')->first();
        $this->assertNotNull($trx);
        $this->assertEquals(TransactionTypeEnum::EXPENSE, $trx->type);
        $this->assertNotNull($trx->proof_image_path);

        Storage::disk('public')->assertExists($trx->proof_image_path);
    }

    public function test_expense_transaction_rejects_income_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'type'        => 'expense',
            'date'        => now()->toDateString(),
            'category_id' => $this->catIncome->id, // Income category!
            'outlet_id'   => $this->outlet1->id,
            'amount'      => 300000,
            'payer_name'  => 'Test Mismatch Expense',
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_staff_cannot_edit_expense_transaction_older_than_7_days(): void
    {
        $oldDate = now()->subDays(10)->toDateString();

        $oldExpense = Transaction::create([
            'type'        => TransactionTypeEnum::EXPENSE,
            'category_id' => $this->catExpense->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => $oldDate,
            'amount'      => 600000,
            'payer_name'  => 'Pengeluaran Lama 10 Hari',
            'created_by'  => $this->staff1->id,
            'created_at'  => now()->subDays(10),
        ]);

        $responseEdit = $this->actingAs($this->staff1)->get(route('transactions.edit', $oldExpense));
        $responseEdit->assertStatus(403);

        $responseDelete = $this->actingAs($this->staff1)->delete(route('transactions.destroy', $oldExpense));
        $responseDelete->assertStatus(403);
    }

    public function test_create_expense_form_renders_expense_categories_only(): void
    {
        $response = $this->actingAs($this->admin)->get(route('transactions.create', ['type' => 'expense']));
        $response->assertStatus(200);
        $response->assertSee('Belanja Stok');
        $response->assertDontSee('Setoran Penjualan Outlet');
    }
}
