<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['nomor_induk' => '2313025068'],
            [
                'name' => 'Vira Azmi aza',
                'email' => 'guru@codequest.test',
                'password' => Hash::make('password123'),
                'role' => 1,
            ]
        );

        User::firstOrCreate(
            ['nomor_induk' => '2313025002'],
            [
                'name' => 'Rizky Febrian',
                'email' => 'siswa@codequest.test',
                'password' => Hash::make('password123'),
                'role' => 0,
            ]
        );
    }
}
