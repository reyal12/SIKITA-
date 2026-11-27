<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPatientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_legacy_registration_and_rescheduling_admin_routes_are_removed(): void
    {
        $admin = User::factory()->create();
        $admin->peran()->attach(Peran::where('kode', 'admin')->value('id'));

        foreach ([
            ['GET', '/admin/pendaftaran'],
            ['GET', '/admin/pendaftaran/create'],
            ['POST', '/admin/pendaftaran'],
            ['GET', '/admin/pendaftaran/1/edit'],
            ['GET', '/admin/pendaftaran/janji-temu/1/edit'],
            ['PUT', '/admin/pendaftaran/1'],
            ['PUT', '/admin/pendaftaran/janji-temu/1'],
            ['PATCH', '/admin/pendaftaran/janji-temu/1/batal'],
            ['DELETE', '/admin/pendaftaran/1'],
            ['GET', '/admin/jadwal-tersedia'],
        ] as [$method, $uri]) {
            $this->actingAs($admin)->call($method, $uri)->assertNotFound();
        }
    }
}
