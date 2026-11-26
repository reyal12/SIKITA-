<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDokterRequest extends FormRequest
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
            'doctor_number' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:dokter,nomor_dokter'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'specialty_id' => ['required', 'integer', 'exists:spesialis,id'],
            'license_number' => ['required', 'string', 'max:50', 'unique:dokter,nomor_izin_praktik'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doctor_number.required' => 'Nomor dokter wajib diisi.',
            'doctor_number.unique' => 'Nomor dokter sudah digunakan.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Email akun sudah digunakan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'specialty_id.required' => 'Spesialisasi wajib dipilih.',
            'specialty_id.exists' => 'Spesialisasi yang dipilih tidak tersedia.',
            'license_number.required' => 'Nomor izin praktik wajib diisi.',
            'license_number.unique' => 'Nomor izin praktik sudah terdaftar.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'doctor_number' => 'nomor dokter',
            'name' => 'nama lengkap',
            'email' => 'email akun',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'specialty_id' => 'spesialisasi',
            'license_number' => 'nomor izin praktik',
            'phone' => 'nomor telepon',
        ];
    }
}
