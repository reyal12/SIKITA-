<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_identitas' => [
                'nullable',
                'string',
                'digits:16',
                Rule::unique('pasien', 'nomor_identitas')->ignore($this->route('patient')),
            ],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'jenis_kelamin' => ['nullable', Rule::in(['laki-laki', 'perempuan', 'lainnya'])],
            'telepon' => ['nullable', 'string', 'max:30', 'regex:/^\\+?[0-9][0-9\\s().-]{6,28}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'golongan_darah' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_identitas.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'nomor_identitas.unique' => 'NIK sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'jenis_kelamin.in' => 'Pilih jenis kelamin yang tersedia.',
            'telepon.regex' => 'Format nomor telepon tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'golongan_darah.in' => 'Pilih golongan darah yang tersedia.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nomor_identitas' => 'NIK',
            'nama_lengkap' => 'nama lengkap',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'telepon' => 'nomor telepon',
            'golongan_darah' => 'golongan darah',
        ];
    }
}
