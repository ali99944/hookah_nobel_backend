<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Manager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('managers')->truncate();

        Manager::create([
            'name' => 'Hookah Nobel',
            'email' => 'admin@nobel.com',
            'password' => Hash::make('nobel'),
        ]);
    }
}
