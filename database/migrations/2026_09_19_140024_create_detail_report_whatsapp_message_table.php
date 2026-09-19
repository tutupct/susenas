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
        Schema::create('detail_report_whatsapp_message', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_report_id')
                ->constrained('detail_reports')
                ->cascadeOnDelete();

            $table->foreignId('whatsapp_message_id')
                ->constrained('whatsapp_messages')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'detail_report_id',
                'whatsapp_message_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_report_whatsapp_message');
    }
};
