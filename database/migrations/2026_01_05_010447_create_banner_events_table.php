<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banner_events', function (Blueprint $table) {
            $table->id();
            $table->string('events_id')->nullable();
            $table->string("file_name_id");
            $table->string("file_name");
            $table->string("path");
            $table->timestamps();

            $table->foreign('events_id')->references('id')->on('events')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_events');
    }
};
