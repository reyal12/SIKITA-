<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpesialisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:spesialis,kode'],
            'nama' => ['required', 'string', 'max:255', 'unique:spesialis,nama'],
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
