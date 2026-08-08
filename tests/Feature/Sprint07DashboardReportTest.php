<?php

namespace Tests\Feature;

use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint07DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff1;
    private User $staff2;
    private Outlet $outlet1;
    private Outlet $outlet2;
    private Category $catIncome;
    private Category $catExpense;
    private ReportService $reportService;

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

        $this->reportService = app(ReportService::class);
    }

    public function test_summary_calculation_accuracy(): void
    {
        // Income Rp 5.000.000 di Outlet 1
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => now()->toDateString(),
            'amount'      => 5000000,
            'payer_name'  => 'Pemasukan 1',
            'created_by'  => $this->admin->id,
        ]);

        // Expense Rp 1.800.000 di Outlet 1
        Transaction::create([
            'type'        => TransactionTypeEnum::EXPENSE,
            'category_id' => $this->catExpense->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => now()->toDateString(),
            'amount'      => 1800000,
            'payer_name'  => 'Pengeluaran 1',
            'created_by'  => $this->admin->id,
        ]);

        $summary = $this->reportService->summary([], $this->admin);

        $this->assertEquals(5000000, $summary['total_income']);
        $this->assertEquals(1800000, $summary['total_expense']);
        $this->assertEquals(3200000, $summary['balance']);
    }

    public function test_staff_is_scoped_to_their_outlet_in_dashboard_and_reports(): void
    {
        // Transaksi Outlet 1
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => now()->toDateString(),
            'amount'      => 4000000,
            'payer_name'  => 'Pemasukan Toko 1',
            'created_by'  => $this->staff1->id,
        ]);

        // Transaksi Outlet 2
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet2->id,
            'date'        => now()->toDateString(),
            'amount'      => 9000000,
            'payer_name'  => 'Pemasukan Toko 2',
            'created_by'  => $this->staff2->id,
        ]);

        // Dashboard Staff 1
        $resDashboard = $this->actingAs($this->staff1)->get(route('dashboard'));
        $resDashboard->assertStatus(200);
        $resDashboard->assertSee('Pemasukan Toko 1');
        $resDashboard->assertDontSee('Pemasukan Toko 2');

        // Reports Staff 1
        $resReports = $this->actingAs($this->staff1)->get(route('reports.index'));
        $resReports->assertStatus(200);
        $resReports->assertSee('Pemasukan Toko 1');
        $resReports->assertDontSee('Pemasukan Toko 2');
    }

    public function test_admin_can_filter_dashboard_and_reports_by_outlet(): void
    {
        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet1->id,
            'date'        => now()->toDateString(),
            'amount'      => 3000000,
            'payer_name'  => 'Sales Outlet 1',
            'created_by'  => $this->admin->id,
        ]);

        Transaction::create([
            'type'        => TransactionTypeEnum::INCOME,
            'category_id' => $this->catIncome->id,
            'outlet_id'   => $this->outlet2->id,
            'date'        => now()->toDateString(),
            'amount'      => 7000000,
            'payer_name'  => 'Sales Outlet 2',
            'created_by'  => $this->admin->id,
        ]);

        // Admin filter ke Outlet 1 saja
        $response = $this->actingAs($this->admin)->get(route('dashboard', ['outlet_id' => $this->outlet1->id]));
        $response->assertStatus(200);
        $response->assertSee('Sales Outlet 1');
        $response->assertDontSee('Sales Outlet 2');
    }

    public function test_trend_chart_grouping_adjusts_for_large_date_ranges(): void
    {
        // Rentang > 60 hari
        $from = now()->subDays(90)->toDateString();
        $to = now()->toDateString();

        $trend = $this->reportService->trend(['from' => $from, 'to' => $to], $this->admin);

        // Harus ter-group per bulan (labels format M Y)
        $this->assertNotEmpty($trend['labels']);
        $this->assertGreaterThan(1, count($trend['labels']));
    }
}
