<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpesialisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $specialtyId = (int) $this->route('specialty');

        return [
            'kode' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('spesialis', 'kode')->ignore($specialtyId),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('spesialis', 'nama')->ignore($specialtyId),
            ],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode.required' => 'Kode spesialisasi wajib diisi.',
            'kode.unique' => 'Kode spesialisasi sudah digunakan.',
            'nama.required' => 'Nama spesialisasi wajib diisi.',
            'nama.unique' => 'Nama spesialisasi sudah terdaftar.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kode' => 'kode spesialisasi',
            'nama' => 'nama spesialisasi',
            'deskripsi' => 'deskripsi',
        ];
    }
}
