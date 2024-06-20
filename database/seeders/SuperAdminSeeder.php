<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'administrator',
            'no_telpon' => '1234567890',
            'email' => 'superadmin@mail.com',
            'password' => Hash::make('password123'),
            // Jangan gunakan password yang lemah di produksi '
            'level' => 'super_admin',
            'tgl_buat' => now(),
            'tgl_update' => now(),
        ]);
    }
}
