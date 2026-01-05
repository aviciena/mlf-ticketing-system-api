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
        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();
            $table->string('config_name')->nullable();
            $table->decimal('card_width', 5, 2)->nullable();
            $table->decimal('card_height', 5, 2)->nullable();
            $table->decimal('margin_top', 5, 2)->nullable();
            $table->decimal('margin_bottom', 5, 2)->nullable();
            $table->decimal('margin_left', 5, 2)->nullable();
            $table->decimal('margin_right', 5, 2)->nullable();
            $table->decimal('font_size_name', 5, 2)->nullable();
            $table->decimal('font_size_organizer', 5, 2)->nullable();
            $table->decimal('font_size_ticket_number', 5, 2)->nullable();
            $table->decimal('margin_top_content', 5, 2)->nullable();
            $table->decimal('margin_bottom_content', 5, 2)->nullable();
            $table->decimal('margin_left_content', 5, 2)->nullable();
            $table->decimal('margin_right_content', 5, 2)->nullable();
            $table->decimal('qr_margin', 5, 2)->nullable();
            $table->decimal('qr_width', 5, 2)->nullable();
            $table->boolean('use_layout')->nullable()->default(false);
            $table->longText('layout_base64')->nullable();
            $table->string('layout_file_name')->nullable();
            $table->decimal('layout_width', 5, 2)->nullable();
            $table->decimal('layout_height', 5, 2)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('RESTRICT');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_templates');
    }
};
