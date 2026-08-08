<?php

namespace App\Http\Requests\Admin;

use App\Enums\TransactionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categories')->where(function ($query) {
                    return $query->where('type', $this->input('type'))
                                 ->whereNull('deleted_at');
                })->ignore($categoryId),
            ],
            'type'      => ['required', Rule::enum(TransactionTypeEnum::class)],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max'      => 'Nama kategori tidak boleh lebih dari 150 karakter.',
            'name.unique'   => 'Kategori dengan nama ini sudah ada untuk tipe transaksi tersebut.',
            'type.required' => 'Tipe kategori (Pemasukan / Pengeluaran) wajib dipilih.',
        ];
    }
}
