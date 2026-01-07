<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Venues;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'name' => fake()->name(),
            'role_id' => 1,
            'is_admin' => true
        ]);

        Venues::factory()->create([
            'id' => 'jicc',
            'created_by' => 'admin',
            'title' => 'Jakarta International Convention Center JICC',
            'description' => 'Jakarta International Convention Center JICC',
            'street' => 'Jl. Gatot Subroto No.1, RT.1/RW.3, Gelora, Kecamatan Tanah Abang, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10270',
            'city' => 'Jakarta Selatan',
            'postal_code' => 10270,
            'province' => 'DKI Jakarta',
            'maps_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.371396623496!2d106.8074994!3d-6.214653499999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f6adbd77af01%3A0x23abed373d7987d2!2sJakarta%20Convention%20Center!5e0!3m2!1sen!2sid!4v1766034680186!5m2!1sen!2sid',
            'maps' => 'https://maps.app.goo.gl/AiuXrgAQEkzXHQhm6',
        ]);
    }
}
