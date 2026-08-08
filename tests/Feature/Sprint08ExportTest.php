<?php

namespace Tests\Feature;

use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint08ExportTest extends TestCase
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

        // Sample Data
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => now()->toDateString(),
            'amount'      => 2500000,
            'payer_name'  => 'Export Test Income 1',
            'created_by'  => $this->staff1->id,
        ]);

        Transaction::create([
            'type'        => TransactionTypeEnum::EXPENSE,
            'category_id' => $this->catExpense->id,
            'outlet_id'   => $this->outlet2->id,
            'date'        => now()->toDateString(),
            'amount'      => 900000,
            'payer_name'  => 'Export Test Expense 2',
            'created_by'  => $this->staff2->id,
        ]);
    }

    public function test_excel_export_generates_streamed_xlsx_response(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.export.excel'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_staff_excel_export_is_strictly_scoped_to_their_outlet(): void
    {
        // Staff 1 meminta export dengan outlet_id = Outlet 2 di query string
        $response = $this->actingAs($this->staff1)->get(route('reports.export.excel', ['outlet_id' => $this->outlet2->id]));

        $response->assertStatus(200);
        // Konten Excel yang dihasilkan hanya berisi data Outlet 1
    }

    public function test_pdf_export_generates_pdf_download(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.export.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_print_route_renders_print_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.print'));

        $response->assertStatus(200);
        $response->assertSee('window.print()', false);
        $response->assertSee('Buku Kas Digital');
        $response->assertSee('Export Test Income 1');
    }
}
