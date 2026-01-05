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
        Schema::create('holders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable(2);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('sex')->nullable();
            $table->string('organization')->nullable();
            $table->string('position')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('holder_categories')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holders');
    }
};
