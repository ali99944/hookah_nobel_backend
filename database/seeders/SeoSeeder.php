<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('seos')->truncate();
    }
}
