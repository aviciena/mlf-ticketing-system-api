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
        Schema::create('validity_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('validity_tickets')->insert([
            ['code' => 'sd', 'description' => 'Single Day'],
            ['code' => 'ad', 'description' => 'Any Day'],
            ['code' => 'adt', 'description' => 'All Day'],
            ['code' => 'mdr', 'description' => 'Multi Date Range']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validity_tickets');
    }
};
