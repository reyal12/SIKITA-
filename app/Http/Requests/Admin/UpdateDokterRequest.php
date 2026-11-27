<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateDokterRequest extends FormRequest
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
        $doctorId = (int) $this->route('doctor');
        $userId = DB::table('dokter')->where('id', $doctorId)->value('pengguna_id');

        return [
            'doctor_number' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('dokter', 'nomor_dokter')->ignore($doctorId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => $userId === null
                ? ['nullable', 'email', 'max:255', 'unique:users,email']
                : [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($userId),
                ],
            'specialty_id' => ['required', 'integer', 'exists:spesialis,id'],
            'license_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('dokter', 'nomor_izin_praktik')->ignore($doctorId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'aktif' => ['required', 'boolean'],
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
            'email.required' => 'Email akun wajib diisi untuk dokter yang memiliki akun.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Email akun sudah digunakan.',
            'specialty_id.required' => 'Spesialisasi wajib dipilih.',
            'specialty_id.exists' => 'Spesialisasi yang dipilih tidak tersedia.',
            'license_number.required' => 'Nomor izin praktik wajib diisi.',
            'license_number.unique' => 'Nomor izin praktik sudah terdaftar.',
            'aktif.required' => 'Status dokter wajib dipilih.',
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
            'specialty_id' => 'spesialisasi',
            'license_number' => 'nomor izin praktik',
            'phone' => 'nomor telepon',
            'aktif' => 'status dokter',
        ];
    }
}
