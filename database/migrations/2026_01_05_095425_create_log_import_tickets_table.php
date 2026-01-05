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
        Schema::create('log_import_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->nullable();
            $table->unsignedBigInteger('events_ticket_id')->nullable();
            $table->string('events_ticket_title')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_import_tickets');
    }
};
