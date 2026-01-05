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
        Schema::create('gate_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id');
            $table->unsignedBigInteger('checkin_gate_id');
            $table->unsignedBigInteger('checkout_gate_id')->nullable();
            $table->datetime('check_in_date')->nullable();
            $table->datetime('check_out_date')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('checkin_gate_id')->references('id')->on('gates')->onDelete('RESTRICT');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('RESTRICT');
            $table->foreign('checkout_gate_id')->references('id')->on('gates')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_tickets');
    }
};
