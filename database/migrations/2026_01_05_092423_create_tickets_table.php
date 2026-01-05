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
        Schema::create('tickets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('events_ticket_id');
            $table->unsignedBigInteger('ticket_status_id');
            $table->unsignedBigInteger('payment_status_id');
            $table->unsignedBigInteger('holder_ticket_id')->nullable();
            $table->unsignedBigInteger('validity_ticket_id');
            $table->dateTime('validity_start_date')->nullable();
            $table->dateTime('validity_end_date')->nullable();
            $table->boolean('allow_multiple_checkin')->default(false);
            $table->unsignedBigInteger('gates_id')->nullable();
            $table->timestamps();

            $table->foreign('events_ticket_id')->references('id')->on('event_tickets')->onDelete('RESTRICT');
            $table->foreign('ticket_status_id')->references('id')->on('ticket_status')->onDelete('RESTRICT');
            $table->foreign('payment_status_id')->references('id')->on('payment_status')->onDelete('RESTRICT');
            $table->foreign('holder_ticket_id')->references('id')->on('holders')->onDelete('RESTRICT');
            $table->foreign('validity_ticket_id')->references('id')->on('validity_tickets')->onDelete('RESTRICT');
            $table->foreign('gates_id')->references('id')->on('gates')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
