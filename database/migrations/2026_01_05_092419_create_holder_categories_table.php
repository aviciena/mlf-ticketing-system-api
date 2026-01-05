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
        Schema::create('holder_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('holder_categories')->insert([
            ['code' => 'organizer', 'description' => 'Organizer'],
            ['code' => 'staff', 'description' => 'Staff'],
            ['code' => 'official', 'description' => 'Official'],
            ['code' => 'exhibitor', 'description' => 'Exhibitor'],
            ['code' => 'invitation', 'description' => 'Invitation'],
            ['code' => 'visitor_online', 'description' => 'Visitor Online'],
            ['code' => 'visitor_ots', 'description' => 'Visitor OTS'],
            ['code' => 'sponsor', 'description' => 'Sponsor'],
            ['code' => 'comitte', 'description' => 'Comitte'],
            ['code' => 'media', 'description' => 'Media']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holder_categories');
    }
};
