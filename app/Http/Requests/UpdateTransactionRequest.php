<?php

namespace App\Http\Requests;

use App\Enums\TransactionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isStaff();
    }

    /**
     * Prepare data before validation (clean raw amount format if string).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            $cleanAmount = preg_replace('/[^0-9]/', '', (string) $this->input('amount'));
            $this->merge([
                'amount' => $cleanAmount !== '' ? (int) $cleanAmount : null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'type'         => ['required', Rule::enum(TransactionTypeEnum::class)],
            'date'         => ['required', 'date', 'before_or_equal:today'],
            'category_id'  => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('type', $this->input('type'))
                          ->where('is_active', true)
                          ->whereNull('deleted_at');
                }),
            ],
            'outlet_id'    => [
                'required',
                Rule::exists('outlets', 'id')->where(function ($query) {
                    $query->where('is_active', true)
                          ->whereNull('deleted_at');
                }),
            ],
            'amount'       => ['required', 'integer', 'min:1'],
            'payer_name'   => ['required', 'string', 'max:150'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'proof_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_proof' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'        => 'Tipe transaksi wajib diisi.',
            'date.required'        => 'Tanggal transaksi wajib diisi.',
            'date.date'            => 'Format tanggal transaksi tidak valid.',
            'date.before_or_equal' => 'Tanggal transaksi tidak boleh melebihi hari ini.',
            'category_id.required' => 'Kategori transaksi wajib dipilih.',
            'category_id.exists'   => 'Kategori yang dipilih tidak valid atau sudah nonaktif.',
            'outlet_id.required'   => 'Outlet transaksi wajib dipilih.',
            'outlet_id.exists'     => 'Outlet yang dipilih tidak valid atau sudah nonaktif.',
            'amount.required'      => 'Jumlah nominal transaksi wajib diisi.',
            'amount.integer'       => 'Nominal transaksi harus berupa angka bulat.',
            'amount.min'           => 'Nominal transaksi minimal Rp 1.',
            'payer_name.required'  => 'Atas Nama penyetor/penerima wajib diisi.',
            'payer_name.max'       => 'Atas Nama maksimal 150 karakter.',
            'description.max'      => 'Keterangan maksimal 1000 karakter.',
            'proof_image.image'    => 'Bukti transaksi harus berupa file gambar.',
            'proof_image.mimes'    => 'Format gambar bukti harus JPG, JPEG, PNG, atau WEBP.',
            'proof_image.max'      => 'Ukuran file gambar bukti maksimal 2MB (2048 KB).',
        ];
    }
}
