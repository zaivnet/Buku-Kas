<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:150'],
            'address'   => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama outlet wajib diisi.',
            'name.max'      => 'Nama outlet tidak boleh lebih dari 150 karakter.',
            'address.max'   => 'Alamat outlet tidak boleh lebih dari 255 karakter.',
        ];
    }
}
