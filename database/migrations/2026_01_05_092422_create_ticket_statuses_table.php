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
        Schema::create('ticket_status', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('ticket_status')->insert([
            ['code' => 'booked', 'description' => 'Booked'],
            ['code' => 'issued', 'description' => 'Issued'],
            ['code' => 'check_in', 'description' => 'Checked-In'],
            ['code' => 'check_out', 'description' => 'Checked-Out'],
            ['code' => 'expired', 'description' => 'Expired'],
            ['code' => 'canceled', 'description' => 'Canceled']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_status');
    }
};
