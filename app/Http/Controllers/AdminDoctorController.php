<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreDokterRequest;
use App\Http\Requests\Admin\StoreJadwalDokterRequest;
use App\Http\Requests\Admin\UpdateDokterRequest;
use App\Http\Requests\Admin\UpdateJadwalDokterRequest;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Peran;
use App\Models\Spesialis;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminDoctorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'specialty_id' => ['nullable', 'integer', 'exists:spesialis,id'],
        ]);

        return view('dashboard.admin.dokter.index', [
            'doctors' => Dokter::query()
                ->with(['spesialis', 'pengguna'])
                ->when($filters['search'] ?? null, function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('nama_lengkap', 'like', '%'.$search.'%')
                            ->orWhere('nomor_dokter', 'like', '%'.$search.'%');
                    });
                })
                ->when(
                    $filters['specialty_id'] ?? null,
                    fn ($query, int $specialtyId) => $query->where('spesialis_id', $specialtyId),
                )
                ->orderBy('nama_lengkap')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
            'specialties' => $this->specialties(),
            'user' => request()->user(),
        ]);
    }

    public function schedules(Request $request): View
    {
        $filters = $request->validate([
            'doctor_id' => ['nullable', 'integer', 'exists:dokter,id'],
            'weekday' => ['nullable', 'integer', 'between:0,6'],
        ]);

        return view('dashboard.admin.jadwal.index', [
            'schedules' => JadwalDokter::query()
                ->with(['dokter.spesialis'])
                ->when(
                    $filters['doctor_id'] ?? null,
                    fn ($query, int $doctorId) => $query->where('dokter_id', $doctorId),
                )
                ->when(
                    isset($filters['weekday']),
                    fn ($query) => $query->where('hari_dalam_minggu', $filters['weekday']),
                )
                ->orderBy('hari_dalam_minggu')
                ->orderBy('waktu_mulai')
                ->paginate(25)
                ->withQueryString(),
            'doctors' => Dokter::query()->orderBy('nama_lengkap')->get(),
            'filters' => $filters,
            'user' => request()->user(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.dokter.form', [
            'doctor' => null,
            'specialties' => $this->specialties(),
            'user' => request()->user(),
        ]);
    }

    public function store(StoreDokterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated): void {
            Spesialis::query()->whereKey($validated['specialty_id'])->lockForUpdate()->firstOrFail();
            $doctorRole = Peran::query()->where('kode', 'dokter')->firstOrFail();
            $account = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
            $account->peran()->attach($doctorRole->id, [
                'ditetapkan_oleh' => $request->user()->id,
            ]);

            Dokter::query()->create([
                'pengguna_id' => $account->id,
                'spesialis_id' => $validated['specialty_id'],
                'nomor_dokter' => $validated['doctor_number'],
                'nama_lengkap' => $validated['name'],
                'nomor_izin_praktik' => $validated['license_number'],
                'telepon' => $validated['phone'] ?? null,
                'aktif' => true,
            ]);
        });

        return redirect()->route('admin.dokter.index')
            ->with('sukses', 'Akun dan data dokter berhasil dibuat. Silakan tambahkan jadwal praktik.');
    }

    public function edit(int $doctor): View
    {
        $doctorRecord = Dokter::query()->with('pengguna')->findOrFail($doctor);

        return view('dashboard.admin.dokter.form', [
            'doctor' => $doctorRecord,
            'specialties' => $this->specialties(),
            'user' => request()->user(),
        ]);
    }

    public function update(UpdateDokterRequest $request, int $doctor): RedirectResponse
    {
        $doctorRecord = Dokter::query()->with('pengguna')->findOrFail($doctor);
        $validated = $request->validated();

        if (! $validated['aktif'] && $this->hasUpcomingAppointments($doctor)) {
            throw ValidationException::withMessages([
                'aktif' => 'Dokter masih memiliki janji temu mendatang. Batalkan atau pindahkan janji temu tersebut sebelum menonaktifkan dokter.',
            ]);
        }

        DB::transaction(function () use ($doctorRecord, $validated): void {
            Spesialis::query()->whereKey($validated['specialty_id'])->lockForUpdate()->firstOrFail();

            $doctorRecord->fill([
                'spesialis_id' => $validated['specialty_id'],
                'nomor_dokter' => $validated['doctor_number'],
                'nama_lengkap' => $validated['name'],
                'nomor_izin_praktik' => $validated['license_number'],
                'telepon' => $validated['phone'] ?? null,
                'aktif' => (bool) $validated['aktif'],
            ])->save();

            if ($doctorRecord->pengguna !== null) {
                $doctorRecord->pengguna->fill([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ])->save();
            }
        });

        return redirect()->route('admin.dokter.edit', $doctor)
            ->with('sukses', 'Data dokter berhasil diperbarui.');
    }

    public function destroy(int $doctor): RedirectResponse
    {
        DB::transaction(function () use ($doctor): void {
            $doctorRecord = Dokter::query()->with('pengguna')->lockForUpdate()->findOrFail($doctor);

            if ($this->doctorHasReferences($doctorRecord)) {
                throw ValidationException::withMessages([
                    'doctor' => 'Dokter memiliki jadwal, janji temu, atau pemeriksaan sehingga tidak dapat dihapus permanen. Gunakan tombol Nonaktifkan untuk mempertahankan riwayat.',
                ]);
            }

            $account = $doctorRecord->pengguna;
            $doctorRecord->delete();

            if ($account !== null) {
                $doctorRoleId = Peran::query()->where('kode', 'dokter')->value('id');
                if ($doctorRoleId !== null) {
                    $account->peran()->detach($doctorRoleId);
                }

                if (! $account->peran()->exists()) {
                    $account->delete();
                }
            }
        });

        return redirect()->route('admin.dokter.index')
            ->with('sukses', 'Data dokter berhasil dihapus.');
    }

    public function deactivate(int $doctor): RedirectResponse
    {
        $doctorRecord = Dokter::query()->findOrFail($doctor);

        if ($this->hasUpcomingAppointments($doctor)) {
            throw ValidationException::withMessages([
                'doctor' => 'Dokter masih memiliki janji temu mendatang. Batalkan atau pindahkan janji temu tersebut sebelum menonaktifkan dokter.',
            ]);
        }

        $doctorRecord->update(['aktif' => false]);

        return redirect()->route('admin.dokter.index')
            ->with('sukses', 'Dokter berhasil dinonaktifkan.');
    }

    public function storeSchedule(StoreJadwalDokterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $doctor = Dokter::query()
                ->whereKey($validated['doctor_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $doctor->aktif) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'Jadwal hanya dapat dibuat untuk dokter aktif.',
                ]);
            }

            $this->ensureScheduleDoesNotOverlap((int) $doctor->id, $validated);
            JadwalDokter::query()->create($this->scheduleAttributes($validated));
        });

        return redirect()->route('admin.jadwal-dokter.index')
            ->with('sukses', 'Jadwal praktik berhasil ditambahkan.');
    }

    public function updateSchedule(UpdateJadwalDokterRequest $request, int $schedule): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($schedule, $validated): void {
            $scheduleRecord = JadwalDokter::query()->lockForUpdate()->findOrFail($schedule);
            $doctorIds = collect([$scheduleRecord->dokter_id, $validated['doctor_id']])
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->sort()
                ->values();
            Dokter::query()->whereIn('id', $doctorIds)->orderBy('id')->lockForUpdate()->get();
            $doctor = Dokter::query()->findOrFail($validated['doctor_id']);

            if (! $doctor->aktif && (int) $doctor->id !== (int) $scheduleRecord->dokter_id) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'Jadwal hanya dapat dipindahkan ke dokter yang aktif.',
                ]);
            }

            $this->ensureScheduleDoesNotOverlap((int) $doctor->id, $validated, $schedule);
            $this->ensureScheduleChangePreservesAppointments($scheduleRecord, $validated);
            $scheduleRecord->update($this->scheduleAttributes($validated));
        });

        return redirect()->route('admin.jadwal-dokter.index')
            ->with('sukses', 'Jadwal praktik berhasil diperbarui.');
    }

    public function destroySchedule(int $schedule): RedirectResponse
    {
        DB::transaction(function () use ($schedule): void {
            $scheduleRecord = JadwalDokter::query()->lockForUpdate()->findOrFail($schedule);
            Dokter::query()->whereKey($scheduleRecord->dokter_id)->lockForUpdate()->firstOrFail();

            if ($this->scheduleHasAppointments($scheduleRecord)) {
                throw ValidationException::withMessages([
                    'schedule' => 'Jadwal ini sudah digunakan oleh janji temu dan tidak dapat dihapus.',
                ]);
            }

            $scheduleRecord->delete();
        });

        return redirect()->route('admin.jadwal-dokter.index')
            ->with('sukses', 'Jadwal praktik berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function ensureScheduleDoesNotOverlap(int $doctor, array $validated, ?int $exceptSchedule = null): void
    {
        $overlapQuery = JadwalDokter::query()
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
    private function ensureScheduleChangePreservesAppointments(JadwalDokter $schedule, array $validated): void
    {
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];
        $weekday = (int) $validated['weekday'];
        $duration = (int) $validated['appointment_duration'];
        $maxPatients = (int) $validated['max_patients'];
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

        if ((int) $validated['doctor_id'] !== (int) $schedule->dokter_id && $bookedAppointments->isNotEmpty()) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Dokter tidak dapat diganti karena jadwal ini memiliki janji temu mendatang.',
            ]);
        }

        foreach ($bookedAppointments as $appointmentsForDate) {
            if ($appointmentsForDate->count() > $maxPatients) {
                throw ValidationException::withMessages([
                    'max_patients' => 'Kuota baru lebih kecil dari jumlah pasien yang sudah terdaftar pada jadwal mendatang.',
                ]);
            }

            foreach ($appointmentsForDate as $appointment) {
                $appointmentTime = substr($appointment->waktu_mulai, 0, 5);
                $appointmentMinutes = ((int) substr($appointmentTime, 0, 2) * 60)
                    + (int) substr($appointmentTime, 3, 2);
                $scheduleStartMinutes = ((int) substr($startTime, 0, 2) * 60)
                    + (int) substr($startTime, 3, 2);
                $endMinutes = ((int) substr($endTime, 0, 2) * 60)
                    + (int) substr($endTime, 3, 2);

                if ($weekday !== (int) $schedule->hari_dalam_minggu
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
            'dokter_id' => $validated['doctor_id'],
            'hari_dalam_minggu' => $validated['weekday'],
            'waktu_mulai' => $validated['start_time'],
            'waktu_selesai' => $validated['end_time'],
            'durasi_janji_menit' => $validated['appointment_duration'],
            'maksimal_pasien' => $validated['max_patients'],
        ];
    }

    private function doctorHasReferences(Dokter $doctor): bool
    {
        return $doctor->jadwal()->exists()
            || DB::table('janji_temu')->where('dokter_id', $doctor->id)->exists()
            || DB::table('rekam_medis')->where('dokter_id', $doctor->id)->exists();
    }

    private function hasUpcomingAppointments(int $doctor): bool
    {
        return DB::table('janji_temu')
            ->where('dokter_id', $doctor)
            ->where('status', 'terjadwal')
            ->whereDate('tanggal_janji', '>=', today())
            ->exists();
    }

    private function scheduleHasAppointments(JadwalDokter $schedule): bool
    {
        return DB::table('janji_temu')
            ->where('dokter_id', $schedule->dokter_id)
            ->whereNotNull('waktu_mulai')
            ->get(['tanggal_janji', 'waktu_mulai'])
            ->contains(function (object $appointment) use ($schedule): bool {
                $appointmentDate = Carbon::parse($appointment->tanggal_janji);
                $appointmentTime = substr($appointment->waktu_mulai, 0, 5);

                return $appointmentDate->dayOfWeek === (int) $schedule->hari_dalam_minggu
                    && $appointmentTime >= substr($schedule->waktu_mulai, 0, 5)
                    && $appointmentTime < substr($schedule->waktu_selesai, 0, 5);
            });
    }

    private function specialties(): Collection
    {
        return Spesialis::query()->orderBy('nama')->get(['id', 'kode', 'nama', 'deskripsi']);
    }
}
