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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('username')->unique()->notNullable();
            $table->string('password')->notNullable();
            $table->boolean('is_admin')->default(false);
            $table->unsignedBigInteger('role_id')->default(2);
            $table->string('event_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('RESTRICT');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
