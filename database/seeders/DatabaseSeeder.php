<?php

namespace Database\Seeders;

use App\Models\ParkingSpace;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Ronald Frangulyan',
            'email' => 'ronald.frangulyan@gmail.com',
            'is_admin' => false,
            'password' => 'zaq1@WSX'
        ]);

        User::factory()->create([
            'name' => 'Jon Doe',
            'email' => 'jon.doe@mail.com',
            'is_admin' => true,
            'password' => 'zaq1@WSX'
        ]);

        ParkingSpace::factory(10)->create();
    }
}
