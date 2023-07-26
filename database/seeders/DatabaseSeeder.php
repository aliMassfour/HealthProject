<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        DB::table('cities')->insert([
            'name' => 'city one'
        ]);
        DB::table('directorates')->insert([
            'name' => 'dicrectorate one'
        ]);
        DB::table('roles')->insert([
            'name' => 'admin'
        ]);
        DB::table('roles')->insert([
            'name' => 'volunteer'
        ]);
        DB::table('users')->insert([
            'name' => 'admin' ,
            'username' => 'admin' ,
            'password' => Hash::make('admin'),
            'phone' => '12345678' ,
            'role_id' =>1,
            'city_id' => 1 ,
            'directorate_id' => 1
        ]);
    }
}
