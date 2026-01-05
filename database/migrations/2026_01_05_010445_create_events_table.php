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
        Schema::create('events', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('parent_id')->nullable();
            $table->string('venue_id');
            $table->unsignedBigInteger('status_id');
            $table->string('title');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->string('icon')->nullable();
            $table->boolean('auto_sync')->default(false);
            $table->boolean('is_sync_interval')->default(false);
            $table->text('sync_query')->nullable();
            $table->string('event_external_id')->nullable();
            $table->string('endpoint')->nullable();
            $table->string('api_key')->nullable();
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('RESTRICT');
            $table->foreign('status_id')->references('id')->on('event_status')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
