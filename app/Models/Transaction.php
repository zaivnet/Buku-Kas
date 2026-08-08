<?php

namespace App\Models;

use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'category_id',
        'outlet_id',
        'date',
        'amount',
        'payer_name',
        'description',
        'proof_image_path',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'   => TransactionTypeEnum::class,
            'date'   => 'date',
            'amount' => 'integer',
        ];
    }

    /**
     * Relasi ke Kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke Outlet.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * User yang mencatat transaksi (creator).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User yang terakhir mengubah transaksi (updater).
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope multi-tenancy outlet untuk user login.
     * Staff otomatis hanya bisa akses outlet miliknya.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isStaff()) {
            return $query->where('outlet_id', $user->outlet_id);
        }

        return $query;
    }

    /**
     * Scope transaksi pemasukan (income).
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', TransactionTypeEnum::INCOME);
    }

    /**
     * Scope transaksi pengeluaran (expense).
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', TransactionTypeEnum::EXPENSE);
    }

    /**
     * Scope filter rentang tanggal.
     */
    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        return $query;
    }

    // ==========================================
    // ACCESSORS / HELPERS
    // ==========================================

    /**
     * Accessor format Rupiah (contoh: "Rp 1.500.000").
     */
    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp ' . number_format($this->amount ?? 0, 0, ',', '.')
        );
    }

    /**
     * Accessor URL bukti gambar (relatif ke storage/public).
     */
    protected function proofImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->proof_image_path ? Storage::disk('public')->url($this->proof_image_path) : null
        );
    }
}
