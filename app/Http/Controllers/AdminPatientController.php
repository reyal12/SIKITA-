<?php

namespace App\Http\Controllers;

use App\AppointmentAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPatientController extends Controller
{
    public function __construct(private AppointmentAvailability $appointmentAvailability) {}

    public function index(): View
    {
        $filters = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tanggal_janji' => ['nullable', 'date_format:Y-m-d'],
            'status_janji' => ['nullable', Rule::in(['terjadwal', 'dikonfirmasi', 'selesai', 'dibatalkan', 'tidak_hadir'])],
        ]);
        $latestAppointments = DB::table('janji_temu')
            ->select('pasien_id')
            ->selectRaw('MAX(id) as latest_id')
            ->groupBy('pasien_id');

        $patients = DB::table('pasien as p')
            ->leftJoinSub($latestAppointments, 'latest_appointments', 'latest_appointments.pasien_id', '=', 'p.id')
            ->leftJoin('janji_temu as j', 'j.id', '=', 'latest_appointments.latest_id')
            ->leftJoin('dokter as d', 'd.id', '=', 'j.dokter_id')
            ->whereNull('p.deleted_at')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('p.nama_lengkap', 'like', '%'.$search.'%')
                        ->orWhere('p.nomor_pasien', 'like', '%'.$search.'%')
                        ->orWhere('p.email', 'like', '%'.$search.'%')
                        ->orWhere('p.telepon', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['tanggal_janji'] ?? null, fn ($query, string $date) => $query->whereDate('j.tanggal_janji', $date))
            ->when($filters['status_janji'] ?? null, fn ($query, string $status) => $query->where('j.status', $status))
            ->select([
                'p.id',
                'p.nomor_pasien',
                'p.nomor_identitas',
                'p.nama_lengkap',
                'p.email',
                'p.telepon',
                'j.nomor_janji',
                'j.tanggal_janji',
                'j.waktu_mulai',
                'j.status as status_janji',
                'd.nama_lengkap as nama_dokter',
            ])
            ->orderBy('p.nama_lengkap')
            ->paginate(15);

        return view('dashboard.admin.pendaftaran.index', [
            'patients' => $patients,
            'filters' => $filters,
            'user' => request()->user(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.pendaftaran.form', [
            'patient' => null,
            'doctors' => $this->activeDoctors(),
            'user' => request()->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($request, $validated): void {
            $patientId = DB::table('pasien')->insertGetId([
                ...$this->patientAttributes($validated),
                'nomor_pasien' => 'PSN-'.Str::upper(Str::random(12)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (empty($validated['doctor_id'])) {
                return;
            }

            $this->createAppointment($patientId, (int) $request->user()->id, $validated);
        });

        $message = empty($validated['doctor_id'])
            ? 'Data pasien berhasil didaftarkan.'
            : 'Data pasien dan janji temu berhasil didaftarkan.';

        return redirect()->route('admin.pendaftaran.index')
            ->with('sukses', $message);
    }

    public function edit(int $patient): View
    {
        return view('dashboard.admin.pendaftaran.form', [
            'patient' => $this->findPatient($patient),
            'doctors' => $this->activeDoctors(),
            'appointments' => DB::table('janji_temu as j')
                ->join('dokter as d', 'd.id', '=', 'j.dokter_id')
                ->where('j.pasien_id', $patient)
                ->orderByDesc('j.tanggal_janji')
                ->orderByDesc('j.waktu_mulai')
                ->get([
                    'j.id',
                    'j.nomor_janji',
                    'j.tanggal_janji',
                    'j.waktu_mulai',
                    'j.status',
                    'j.keluhan_utama',
                    'd.nama_lengkap as nama_dokter',
                ]),
            'user' => request()->user(),
        ]);
    }

    public function update(Request $request, int $patient): RedirectResponse
    {
        $patientRecord = $this->findPatient($patient);
        $validated = $request->validate($this->rules(patientId: $patient));

        DB::transaction(function () use ($request, $patientRecord, $validated): void {
            DB::table('pasien')
                ->where('id', $patientRecord->id)
                ->whereNull('deleted_at')
                ->update($this->patientAttributes($validated) + ['updated_at' => now()]);

            if (! empty($validated['doctor_id'])) {
                $this->createAppointment((int) $patientRecord->id, (int) $request->user()->id, $validated);
            }
        });

        $message = empty($validated['doctor_id'])
            ? 'Data pasien berhasil diperbarui.'
            : 'Data pasien diperbarui dan janji temu baru berhasil dibuat.';

        return redirect()->route('admin.pendaftaran.index')->with('sukses', $message);
    }

    public function editAppointment(int $appointment): View
    {
        $appointmentRecord = $this->findAppointment($appointment);

        return view('dashboard.admin.pendaftaran.janji-temu', [
            'appointment' => $appointmentRecord,
            'doctors' => $this->activeDoctors(),
            'user' => request()->user(),
        ]);
    }

    public function destroy(int $patient): RedirectResponse
    {
        $patientRecord = $this->findPatient($patient);

        if (DB::table('janji_temu')
            ->where('pasien_id', $patient)
            ->where('status', 'terjadwal')
            ->whereDate('tanggal_janji', '>=', today())
            ->exists()) {
            throw ValidationException::withMessages([
                'patient' => 'Pasien masih memiliki janji temu mendatang. Batalkan atau pindahkan jadwal sebelum menghapus data pasien.',
            ]);
        }

        DB::table('pasien')
            ->where('id', $patientRecord->id)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.pendaftaran.index')
            ->with('sukses', 'Data pasien berhasil dihapus.');
    }

    public function availableSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:dokter,id'],
            'appointment_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.now()->addDays(90)->toDateString(),
            ],
            'appointment_id' => ['nullable', 'integer', 'exists:janji_temu,id'],
        ]);

        $doctorIsActive = DB::table('dokter')
            ->where('id', $validated['doctor_id'])
            ->where('aktif', true)
            ->exists();

        if (! $doctorIsActive) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Dokter yang dipilih tidak tersedia.',
            ]);
        }

        if (! empty($validated['appointment_id'])) {
            $appointment = DB::table('janji_temu')
                ->join('pasien', 'pasien.id', '=', 'janji_temu.pasien_id')
                ->where('janji_temu.id', $validated['appointment_id'])
                ->whereNull('pasien.deleted_at')
                ->where('janji_temu.status', 'terjadwal')
                ->whereDate('janji_temu.tanggal_janji', '>=', today())
                ->exists();

            if (! $appointment) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'Janji temu ini tidak dapat diubah.',
                ]);
            }
        }

        return response()->json([
            'slots' => $this->appointmentAvailability->availableTimes(
                (int) $validated['doctor_id'],
                $validated['appointment_date'],
                exceptAppointmentId: isset($validated['appointment_id'])
                    ? (int) $validated['appointment_id']
                    : null,
            ),
        ]);
    }

    public function reschedule(Request $request, int $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:dokter,id'],
            'appointment_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.now()->addDays(90)->toDateString(),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'chief_complaint' => ['nullable', 'string', 'max:2000'],
        ]);

        $patientId = DB::transaction(function () use ($appointment, $validated): int {
            DB::table('janji_temu')->where('id', $appointment)->lockForUpdate()->first();

            $currentAppointment = DB::table('janji_temu')
                ->join('pasien', 'pasien.id', '=', 'janji_temu.pasien_id')
                ->where('janji_temu.id', $appointment)
                ->whereNull('pasien.deleted_at')
                ->where('janji_temu.status', 'terjadwal')
                ->whereDate('janji_temu.tanggal_janji', '>=', today())
                ->select('janji_temu.pasien_id')
                ->first();

            if ($currentAppointment === null) {
                abort(404);
            }

            $doctorId = (int) $validated['doctor_id'];
            $doctorIsActive = DB::table('dokter')
                ->where('id', $doctorId)
                ->where('aktif', true)
                ->exists();

            if (! $doctorIsActive || ! in_array(
                $validated['start_time'],
                $this->appointmentAvailability->availableTimes(
                    $doctorId,
                    $validated['appointment_date'],
                    true,
                    $appointment,
                ),
                true,
            )) {
                throw ValidationException::withMessages([
                    'start_time' => 'Jadwal dokter tidak tersedia. Silakan pilih dokter, tanggal, dan jam lain.',
                ]);
            }

            $queueNumber = ((int) DB::table('janji_temu')
                ->where('dokter_id', $doctorId)
                ->whereDate('tanggal_janji', $validated['appointment_date'])
                ->where('id', '!=', $appointment)
                ->max('nomor_antrean')) + 1;

            DB::table('janji_temu')->where('id', $appointment)->update([
                'dokter_id' => $doctorId,
                'tanggal_janji' => $validated['appointment_date'],
                'waktu_mulai' => $validated['start_time'],
                'nomor_antrean' => $queueNumber,
                'keluhan_utama' => $validated['chief_complaint'] ?? null,
                'updated_at' => now(),
            ]);

            return (int) $currentAppointment->pasien_id;
        });

        return redirect()->route('admin.pendaftaran.edit', $patientId)
            ->with('sukses', 'Janji temu berhasil dijadwalkan ulang.');
    }

    public function cancelAppointment(int $appointment): RedirectResponse
    {
        $updated = DB::table('janji_temu')
            ->where('id', $appointment)
            ->where('status', 'terjadwal')
            ->whereDate('tanggal_janji', '>=', today())
            ->update([
                'status' => 'dibatalkan',
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            abort(404);
        }

        $patientId = (int) DB::table('janji_temu')->where('id', $appointment)->value('pasien_id');

        return redirect()->route('admin.pendaftaran.edit', $patientId)
            ->with('sukses', 'Janji temu berhasil dibatalkan.');
    }

    /**
     * @return array<string, array<int, string|Unique>>
     */
    private function rules(?int $patientId = null): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nomor_identitas' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('pasien', 'nomor_identitas')->ignore($patientId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan,lainnya'],
            'golongan_darah' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'doctor_id' => [
                'nullable',
                'integer',
                'exists:dokter,id',
                'required_with:appointment_date,start_time',
            ],
            'appointment_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.now()->addDays(90)->toDateString(),
                'required_with:doctor_id,start_time',
            ],
            'start_time' => [
                'nullable',
                'date_format:H:i',
                'required_with:doctor_id,appointment_date',
            ],
            'chief_complaint' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function patientAttributes(array $validated): array
    {
        return [
            'nama_lengkap' => $validated['nama_lengkap'],
            'nomor_identitas' => $validated['nomor_identitas'] ?? null,
            'email' => $validated['email'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'golongan_darah' => $validated['golongan_darah'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ];
    }

    private function createAppointment(int $patientId, int $createdBy, array $validated): void
    {
        $doctorId = (int) $validated['doctor_id'];
        $doctorIsActive = DB::table('dokter')
            ->where('id', $doctorId)
            ->where('aktif', true)
            ->exists();

        if (! $doctorIsActive || ! in_array(
            $validated['start_time'],
            $this->appointmentAvailability->availableTimes(
                $doctorId,
                $validated['appointment_date'],
                true,
            ),
            true,
        )) {
            throw ValidationException::withMessages([
                'start_time' => 'Jadwal dokter tidak tersedia. Silakan pilih dokter, tanggal, dan jam lain.',
            ]);
        }

        $queueNumber = ((int) DB::table('janji_temu')
            ->where('dokter_id', $doctorId)
            ->whereDate('tanggal_janji', $validated['appointment_date'])
            ->max('nomor_antrean')) + 1;

        DB::table('janji_temu')->insert([
            'nomor_janji' => 'JAN-'.Str::upper(Str::random(12)),
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => $validated['appointment_date'],
            'nomor_antrean' => $queueNumber,
            'waktu_mulai' => $validated['start_time'],
            'status' => 'terjadwal',
            'keluhan_utama' => $validated['chief_complaint'] ?? null,
            'dibuat_oleh' => $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function findPatient(int $patientId): object
    {
        $patient = DB::table('pasien')
            ->where('id', $patientId)
            ->whereNull('deleted_at')
            ->first();

        abort_if($patient === null, 404);

        return $patient;
    }

    private function findAppointment(int $appointmentId): object
    {
        $appointment = DB::table('janji_temu as j')
            ->join('pasien as p', 'p.id', '=', 'j.pasien_id')
            ->where('j.id', $appointmentId)
            ->where('j.status', 'terjadwal')
            ->whereDate('j.tanggal_janji', '>=', today())
            ->whereNull('p.deleted_at')
            ->select([
                'j.*',
                'p.nama_lengkap as nama_pasien',
            ])
            ->first();

        abort_if($appointment === null, 404);

        return $appointment;
    }

    private function activeDoctors(): Collection
    {
        return DB::table('dokter as d')
            ->join('spesialis as s', 's.id', '=', 'd.spesialis_id')
            ->where('d.aktif', true)
            ->orderBy('d.nama_lengkap')
            ->get([
                'd.id',
                'd.nama_lengkap',
                's.nama as nama_spesialis',
            ]);
    }
}
