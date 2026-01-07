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
        Schema::create('event_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('event_id');
            $table->string('title');
            $table->unsignedBigInteger('event_ticket_category_id');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->datetime('sale_start_date')->nullable();
            $table->datetime('sale_end_date')->nullable();
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->default(1);
            $table->integer('quota')->default(1000);
            $table->integer('price')->nullable();
            $table->integer('original_price')->nullable();
            $table->string('discount_type')->nullable('percentage');
            $table->integer('discount_amount')->nullable();
            $table->integer('price_after_discount')->nullable();
            $table->boolean('allow_multiple_checkin')->default(true);
            $table->unsignedBigInteger('validity_type_id')->default(1);
            $table->boolean('auto_checkout')->default(false);
            $table->string('external_event_ticket_id')->nullable();
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('RESTRICT');
            $table->foreign('event_ticket_category_id')->references('id')->on('events_ticket_categories')->onDelete('RESTRICT');
            $table->foreign('validity_type_id')->references('id')->on('validity_tickets')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_tickets');
    }
};
