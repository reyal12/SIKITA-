<?php

namespace App\Http\Controllers;

use App\Models\Peran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminDoctorController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin.dokter.index', [
            'doctors' => DB::table('dokter as d')
                ->join('spesialis as s', 's.id', '=', 'd.spesialis_id')
                ->leftJoin('users as u', 'u.id', '=', 'd.pengguna_id')
                ->select('d.*', 's.nama', 'u.email')
                ->orderBy('d.nama_lengkap')
                ->paginate(15),
            'user' => request()->user(),
        ]);
    }

    public function schedules(): View
    {
        return view('dashboard.admin.jadwal.index', [
            'schedules' => DB::table('jadwal_dokter as j')
                ->join('dokter as d', 'd.id', '=', 'j.dokter_id')
                ->join('spesialis as s', 's.id', '=', 'd.spesialis_id')
                ->select([
                    'j.dokter_id',
                    'j.hari_dalam_minggu',
                    'j.waktu_mulai',
                    'j.waktu_selesai',
                    'j.durasi_janji_menit',
                    'j.maksimal_pasien',
                    'd.nama_lengkap',
                    'd.aktif',
                    's.nama',
                ])
                ->orderBy('j.hari_dalam_minggu')
                ->orderBy('j.waktu_mulai')
                ->orderBy('d.nama_lengkap')
                ->paginate(25)
                ->withQueryString(),
            'user' => request()->user(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.dokter.form', [
            'doctor' => null,
            'specialties' => $this->specialties(),
            'schedules' => collect(),
            'user' => request()->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'specialty_id' => ['required', 'integer', 'exists:spesialis,id'],
            'license_number' => ['required', 'string', 'max:50', 'unique:dokter,nomor_izin_praktik'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($validated): void {
            $doctorRole = Peran::where('kode', 'dokter')->firstOrFail();
            $account = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            $account->peran()->attach($doctorRole->id);

            DB::table('dokter')->insert([
                'pengguna_id' => $account->id,
                'spesialis_id' => $validated['specialty_id'],
                'nomor_dokter' => 'DR-'.Str::upper(Str::random(12)),
                'nama_lengkap' => $validated['name'],
                'nomor_izin_praktik' => $validated['license_number'],
                'telepon' => $validated['phone'] ?? null,
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $destination = $request->routeIs('admin.akun.dokter.store')
            ? 'admin.akun.index'
            : 'admin.dokter.index';

        return redirect()->route($destination)
            ->with('sukses', 'Akun dan data dokter berhasil dibuat. Silakan tambahkan jadwal praktik.');
    }

    public function edit(int $doctor): View
    {
        $doctorRecord = $this->findDoctor($doctor);

        return view('dashboard.admin.dokter.form', [
            'doctor' => $doctorRecord,
            'specialties' => $this->specialties(),
            'schedules' => DB::table('jadwal_dokter')
                ->where('dokter_id', $doctor)
                ->orderBy('hari_dalam_minggu')
                ->orderBy('waktu_mulai')
                ->get(),
            'user' => request()->user(),
        ]);
    }

    public function update(Request $request, int $doctor): RedirectResponse
    {
        $doctorRecord = $this->findDoctor($doctor);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => $doctorRecord->pengguna_id === null
                ? ['nullable', 'email', 'max:255', 'unique:users,email']
                : ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($doctorRecord->pengguna_id)],
            'specialty_id' => ['required', 'integer', 'exists:spesialis,id'],
            'license_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('dokter', 'nomor_izin_praktik')->ignore($doctor),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'aktif' => ['required', 'boolean'],
        ]);

        if (! $validated['aktif'] && $this->hasUpcomingAppointments($doctor)) {
            throw ValidationException::withMessages([
                'aktif' => 'Dokter masih memiliki janji temu mendatang. Batalkan atau pindahkan janji temu tersebut sebelum menonaktifkan dokter.',
            ]);
        }

        DB::transaction(function () use ($doctor, $doctorRecord, $validated): void {
            if ($doctorRecord->pengguna_id !== null) {
                DB::table('users')->where('id', $doctorRecord->pengguna_id)->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'updated_at' => now(),
                ]);
            }

            DB::table('dokter')->where('id', $doctor)->update([
                'spesialis_id' => $validated['specialty_id'],
                'nama_lengkap' => $validated['name'],
                'nomor_izin_praktik' => $validated['license_number'],
                'telepon' => $validated['phone'] ?? null,
                'aktif' => (bool) $validated['aktif'],
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('admin.dokter.edit', $doctor)
            ->with('sukses', 'Data dokter berhasil diperbarui.');
    }

    public function destroy(int $doctor): RedirectResponse
    {
        $this->findDoctor($doctor);

        if ($this->hasUpcomingAppointments($doctor)) {
            throw ValidationException::withMessages([
                'doctor' => 'Dokter masih memiliki janji temu mendatang. Batalkan atau pindahkan janji temu tersebut sebelum menonaktifkan dokter.',
            ]);
        }

        DB::table('dokter')->where('id', $doctor)->update([
            'aktif' => false,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.dokter.index')
            ->with('sukses', 'Dokter berhasil dinonaktifkan. Riwayat dan akun tetap tersimpan.');
    }

    public function storeSchedule(Request $request, int $doctor): RedirectResponse
    {
        $doctorRecord = $this->findDoctor($doctor);
        abort_unless($doctorRecord->aktif, 404);
        $validated = $request->validate($this->scheduleRules($doctor));

        $this->ensureScheduleDoesNotOverlap($doctor, $validated);

        DB::table('jadwal_dokter')->insert([
            ...$this->scheduleAttributes($validated),
            'dokter_id' => $doctor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.dokter.edit', $doctor)
            ->with('sukses', 'Jadwal praktik berhasil ditambahkan.');
    }

    public function updateSchedule(Request $request, int $schedule): RedirectResponse
    {
        $scheduleRecord = $this->findSchedule($schedule);
        $doctor = (int) $scheduleRecord->dokter_id;
        abort_unless($this->findDoctor($doctor)->aktif, 404);
        $validated = $request->validate($this->scheduleRules($doctor, $schedule));

        $this->ensureScheduleDoesNotOverlap($doctor, $validated, $schedule);
        $this->ensureScheduleChangePreservesAppointments($scheduleRecord, $validated);

        DB::table('jadwal_dokter')->where('id', $schedule)->update(
            $this->scheduleAttributes($validated) + ['updated_at' => now()],
        );

        return redirect()->route('admin.dokter.edit', $doctor)
            ->with('sukses', 'Jadwal praktik berhasil diperbarui.');
    }

    public function destroySchedule(int $schedule): RedirectResponse
    {
        $scheduleRecord = $this->findSchedule($schedule);
        $this->ensureScheduleChangePreservesAppointments($scheduleRecord, []);
        DB::table('jadwal_dokter')->where('id', $schedule)->delete();

        return redirect()->route('admin.dokter.edit', $scheduleRecord->dokter_id)
            ->with('sukses', 'Jadwal praktik berhasil dihapus.');
    }

    public function accounts(): View
    {
        return view('dashboard.admin.akun.index', [
            'accounts' => User::query()->with('peran')->orderBy('name')->paginate(20),
            'user' => request()->user(),
        ]);
    }

    /**
     * @return array<string, array<int, string|Unique>>
     */
    private function scheduleRules(int $doctor, ?int $schedule = null): array
    {
        return [
            'weekday' => ['required', 'integer', 'between:0,6'],
            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('jadwal_dokter', 'waktu_mulai')
                    ->where('dokter_id', $doctor)
                    ->where('hari_dalam_minggu', request()->input('weekday'))
                    ->ignore($schedule),
            ],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'appointment_duration' => ['required', 'integer', 'in:15,20,30,45,60'],
            'max_patients' => ['required', 'integer', 'between:1,500'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function ensureScheduleDoesNotOverlap(int $doctor, array $validated, ?int $exceptSchedule = null): void
    {
        $overlapQuery = DB::table('jadwal_dokter')
            ->where('dokter_id', $doctor)
            ->where('hari_dalam_minggu', $validated['weekday'])
            ->where('waktu_mulai', '<', $validated['end_time'])
            ->where('waktu_selesai', '>', $validated['start_time']);

        if ($exceptSchedule !== null) {
            $overlapQuery->where('id', '!=', $exceptSchedule);
        }

        if ($overlapQuery->exists()) {
            throw ValidationException::withMessages([
                'start_time' => 'Jadwal praktik bertabrakan dengan jadwal lain pada hari tersebut.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function ensureScheduleChangePreservesAppointments(object $schedule, array $validated): void
    {
        $startTime = $validated['start_time'] ?? null;
        $endTime = $validated['end_time'] ?? null;
        $weekday = isset($validated['weekday']) ? (int) $validated['weekday'] : null;
        $duration = isset($validated['appointment_duration'])
            ? (int) $validated['appointment_duration']
            : (int) $schedule->durasi_janji_menit;
        $maxPatients = isset($validated['max_patients']) ? (int) $validated['max_patients'] : null;
        $bookedAppointments = DB::table('janji_temu')
            ->where('dokter_id', $schedule->dokter_id)
            ->where('status', 'terjadwal')
            ->whereDate('tanggal_janji', '>=', today())
            ->whereDate('tanggal_janji', '<=', now()->addDays(90)->toDateString())
            ->get(['tanggal_janji', 'waktu_mulai'])
            ->filter(function (object $appointment) use ($schedule): bool {
                $appointmentDate = Carbon::parse($appointment->tanggal_janji);
                $appointmentTime = substr($appointment->waktu_mulai, 0, 5);

                return $appointmentDate->dayOfWeek === (int) $schedule->hari_dalam_minggu
                    && $appointmentTime >= substr($schedule->waktu_mulai, 0, 5)
                    && $appointmentTime < substr($schedule->waktu_selesai, 0, 5);
            })
            ->groupBy('tanggal_janji');

        foreach ($bookedAppointments as $appointmentsForDate) {
            if ($maxPatients !== null && $appointmentsForDate->count() > $maxPatients) {
                throw ValidationException::withMessages([
                    'max_patients' => 'Kuota baru lebih kecil dari jumlah pasien yang sudah terdaftar pada jadwal mendatang.',
                ]);
            }

            foreach ($appointmentsForDate as $appointment) {
                $appointmentTime = substr($appointment->waktu_mulai, 0, 5);
                $appointmentMinutes = ((int) substr($appointmentTime, 0, 2) * 60)
                    + (int) substr($appointmentTime, 3, 2);
                $scheduleStartMinutes = $startTime === null
                    ? 0
                    : ((int) substr($startTime, 0, 2) * 60) + (int) substr($startTime, 3, 2);
                $endMinutes = $endTime === null
                    ? 0
                    : ((int) substr($endTime, 0, 2) * 60) + (int) substr($endTime, 3, 2);

                if ($startTime === null || $endTime === null || $weekday !== (int) $schedule->hari_dalam_minggu
                    || $appointmentTime < $startTime
                    || $appointmentMinutes + $duration > $endMinutes
                    || ($appointmentMinutes - $scheduleStartMinutes) % $duration !== 0) {
                    throw ValidationException::withMessages([
                        'start_time' => 'Pindahkan atau batalkan janji temu mendatang sebelum mengubah rentang jadwal.',
                    ]);
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function scheduleAttributes(array $validated): array
    {
        return [
            'hari_dalam_minggu' => $validated['weekday'],
            'waktu_mulai' => $validated['start_time'],
            'waktu_selesai' => $validated['end_time'],
            'durasi_janji_menit' => $validated['appointment_duration'],
            'maksimal_pasien' => $validated['max_patients'],
        ];
    }

    private function findDoctor(int $doctor): object
    {
        $record = DB::table('dokter as d')
            ->leftJoin('users as u', 'u.id', '=', 'd.pengguna_id')
            ->where('d.id', $doctor)
            ->select([
                'd.*',
                'u.email',
            ])
            ->first();

        abort_if($record === null, 404);

        return $record;
    }

    private function findSchedule(int $schedule): object
    {
        $record = DB::table('jadwal_dokter')->where('id', $schedule)->first();

        abort_if($record === null, 404);

        return $record;
    }

    private function hasUpcomingAppointments(int $doctor): bool
    {
        return DB::table('janji_temu')
            ->where('dokter_id', $doctor)
            ->where('status', 'terjadwal')
            ->whereDate('tanggal_janji', '>=', today())
            ->exists();
    }

    private function specialties(): Collection
    {
        return DB::table('spesialis')->orderBy('nama')->get(['id', 'nama']);
    }
}
