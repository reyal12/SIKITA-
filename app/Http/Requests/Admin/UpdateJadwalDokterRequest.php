<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJadwalDokterRequest extends FormRequest
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
            'doctor_id' => ['required', 'integer', 'exists:dokter,id'],
            'weekday' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'appointment_duration' => ['required', 'integer', 'between:1,1440'],
            'max_patients' => ['required', 'integer', 'between:1,500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Dokter wajib dipilih.',
            'doctor_id.exists' => 'Dokter yang dipilih tidak tersedia.',
            'weekday.required' => 'Hari praktik wajib dipilih.',
            'weekday.between' => 'Hari praktik tidak valid.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'appointment_duration.required' => 'Durasi janji wajib diisi.',
            'appointment_duration.between' => 'Durasi janji harus lebih dari 0 menit.',
            'max_patients.required' => 'Kuota pasien wajib diisi.',
            'max_patients.between' => 'Kuota pasien harus berupa angka positif.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'doctor_id' => 'dokter',
            'weekday' => 'hari praktik',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'appointment_duration' => 'durasi janji',
            'max_patients' => 'kuota pasien',
        ];
    }
}
