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

class Sprint05IncomeTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff1;
    private User $staff2;
    private Outlet $outlet1;
    private Outlet $outlet2;
    private Category $catIncome;
    private Category $catExpense;

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

        $this->catIncome = Category::where('type', TransactionTypeEnum::INCOME)->first();
        $this->catExpense = Category::where('type', TransactionTypeEnum::EXPENSE)->first();
    }

    public function test_staff_can_view_income_transactions_for_their_outlet_only(): void
    {
        // Transaksi di Outlet 1
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => now()->toDateString(),
            'amount'      => 1000000,
            'payer_name'  => 'Setoran Toko 1',
            'created_by'  => $this->staff1->id,
        ]);

        // Transaksi di Outlet 2
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet2->id,
            'date'        => now()->toDateString(),
            'amount'      => 2000000,
            'payer_name'  => 'Setoran Toko 2',
            'created_by'  => $this->staff2->id,
        ]);

        $response = $this->actingAs($this->staff1)->get(route('transactions.income'));
        $response->assertStatus(200);
        $response->assertSee('Setoran Toko 1');
        $response->assertDontSee('Setoran Toko 2');
    }

    public function test_staff_outlet_is_forced_even_if_manipulated_in_request(): void
    {
        // Staff 1 mencoba kirim outlet_id milik Outlet 2 di request
        $response = $this->actingAs($this->staff1)->post(route('transactions.store'), [
            'type'        => 'income',
            'date'        => now()->toDateString(),
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet2->id, // Manipulasi request ke Outlet 2
            'amount'      => 1500000,
            'payer_name'  => 'Setoran Hack',
        ]);

        $response->assertRedirect(route('transactions.income'));

        // Dipastikan transaksi yang tersimpan tetap terikat pada Outlet 1 milik Staff 1
        $this->assertDatabaseHas('transactions', [
            'payer_name' => 'Setoran Hack',
            'outlet_id'  => $this->outlet1->id,
        ]);
    }

    public function test_income_transaction_with_proof_image_upload_compresses_and_stores(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('bukti_transfer.jpg', 2000, 2000); // 2000x2000 -> resized to 1200px max

        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'type'        => 'income',
            'date'        => now()->toDateString(),
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'amount'      => 3500000,
            'payer_name'  => 'Setoran Outlet 1 via Bank',
            'proof_image' => $file,
        ]);

        $response->assertRedirect(route('transactions.income'));

        $trx = Transaction::where('payer_name', 'Setoran Outlet 1 via Bank')->first();
        $this->assertNotNull($trx);
        $this->assertNotNull($trx->proof_image_path);

        Storage::disk('public')->assertExists($trx->proof_image_path);
    }

    public function test_transaction_without_proof_image_can_be_stored(): void
    {
        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'type'        => 'income',
            'date'        => now()->toDateString(),
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'amount'      => 500000,
            'payer_name'  => 'Setoran Tunai Tanpa Gambar',
        ]);

        $response->assertRedirect(route('transactions.income'));

        $this->assertDatabaseHas('transactions', [
            'payer_name'       => 'Setoran Tunai Tanpa Gambar',
            'proof_image_path' => null,
        ]);
    }

    public function test_staff_cannot_edit_transaction_older_than_7_days(): void
    {
        $oldDate = now()->subDays(10)->toDateString();

        $oldTransaction = Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => $oldDate,
            'amount'      => 1000000,
            'payer_name'  => 'Transaksi Lama 10 Hari',
            'created_by'  => $this->staff1->id,
            'created_at'  => now()->subDays(10),
        ]);

        // Coba edit oleh Staff 1 -> 403 Forbidden
        $responseEdit = $this->actingAs($this->staff1)->get(route('transactions.edit', $oldTransaction));
        $responseEdit->assertStatus(403);

        // Coba update oleh Staff 1 -> 403 Forbidden
        $responseUpdate = $this->actingAs($this->staff1)->put(route('transactions.update', $oldTransaction), [
            'type'        => 'income',
            'date'        => $oldDate,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'amount'      => 2000000,
            'payer_name'  => 'Update Ilegal',
        ]);
        $responseUpdate->assertStatus(403);

        // Namun Admin tetap dapat mengedit transaksi lama tersebut
        $responseAdminEdit = $this->actingAs($this->admin)->get(route('transactions.edit', $oldTransaction));
        $responseAdminEdit->assertStatus(200);
    }

    public function test_category_type_mismatch_is_rejected(): void
    {
        // Mencoba simpan transaksi Income dengan memilih Kategori Expense
        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'type'        => 'income',
            'date'        => now()->toDateString(),
            'category_id' => $this->catExpense->id, // Expense category!
            'outlet_id'   => $this->outlet1->id,
            'amount'      => 500000,
            'payer_name'  => 'Mismatch Test',
        ]);

        $response->assertSessionHasErrors('category_id');
    }
}
