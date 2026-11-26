<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminAccountRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'string',
                Rule::in(['admin', 'dokter', 'pasien']),
                Rule::exists('peran', 'kode'),
            ],
            'specialty_id' => [
                'nullable',
                'required_if:role,dokter',
                'integer',
                'exists:spesialis,id',
            ],
            'license_number' => [
                'nullable',
                'required_if:role,dokter',
                'string',
                'max:50',
                'unique:dokter,nomor_izin_praktik',
            ],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Alamat email sudah digunakan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Peran akun wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak tersedia.',
            'role.exists' => 'Peran belum tersedia di sistem.',
            'specialty_id.required_if' => 'Spesialisasi wajib dipilih untuk akun dokter.',
            'specialty_id.exists' => 'Spesialisasi yang dipilih tidak tersedia.',
            'license_number.required_if' => 'Nomor izin praktik wajib diisi untuk akun dokter.',
            'license_number.unique' => 'Nomor izin praktik sudah terdaftar.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'role' => 'peran',
            'specialty_id' => 'spesialisasi',
            'license_number' => 'nomor izin praktik',
            'phone' => 'nomor telepon',
        ];
    }
}
