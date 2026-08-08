<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name'      => ['required', 'string', 'max:150'],
            'email'     => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['required', Rule::enum(RoleEnum::class)],
            'outlet_id' => [
                'required_if:role,staff',
                'nullable',
                Rule::exists('outlets', 'id')->where(function ($query) {
                    $query->where('is_active', true)->whereNull('deleted_at');
                }),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama pengguna wajib diisi.',
            'email.required'       => 'Alamat email wajib diisi.',
            'email.email'          => 'Format alamat email tidak valid.',
            'email.unique'         => 'Alamat email sudah digunakan oleh pengguna lain.',
            'password.min'         => 'Kata sandi minimal 8 karakter.',
            'role.required'        => 'Peranan (Role) wajib dipilih.',
            'outlet_id.required_if'=> 'Outlet wajib dipilih untuk pengguna dengan role Staff.',
            'outlet_id.exists'     => 'Outlet yang dipilih tidak valid atau sudah nonaktif.',
        ];
    }
}
