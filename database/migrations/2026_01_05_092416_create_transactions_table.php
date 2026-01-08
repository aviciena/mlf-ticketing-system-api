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
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('event_id');
            $table->integer("donation")->nullable();
            $table->integer("fee");
            $table->decimal("subtotal", 15, 2);
            $table->integer("total_ticket");
            $table->decimal('total_price', 15, 2);
            $table->string("payment_type")->nullable();
            $table->string('reference_code')->unique()->nullable();
            $table->enum('status', ['pending', 'settlement', 'cancel'])->default('pending');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
