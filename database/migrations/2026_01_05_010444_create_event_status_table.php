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
        Schema::create('event_status', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('event_status')->insert([
            [
                'code' => 'not_started',
                'description' => 'Not Started',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'active',
                'description' => 'Active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'completed',
                'description' => 'Completed',
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
        Schema::dropIfExists('event_status');
    }
};
