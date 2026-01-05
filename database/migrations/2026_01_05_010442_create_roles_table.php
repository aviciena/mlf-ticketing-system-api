<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('roles')->insert([
            [
                'code' => 'admin',
                'description' => 'Admin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'finance',
                'description' => 'Finance',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'helpdesk',
                'description' => 'Helpdesk',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'scanner',
                'description' => 'Scanner',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'teller',
                'description' => 'Teller',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
