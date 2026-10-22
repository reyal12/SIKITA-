<?php

namespace Database\Seeders;

use App\Models\Peran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminName = config('sikita.admin.name', 'Administrator SIKITA');
        $adminEmail = config('sikita.admin.email', 'admin@sikita.test');
        $adminPassword = config('sikita.admin.password', 'admin12345');

        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        $peranAdmin = Peran::where('kode', 'admin')->first();

        if ($peranAdmin) {
            $user->peran()->syncWithoutDetaching([
                $peranAdmin->id => [
                    'ditetapkan_oleh' => null,
                ],
            ]);
        }
    }
}
