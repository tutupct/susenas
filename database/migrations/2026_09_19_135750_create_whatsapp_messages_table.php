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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petugas_id')->constrained('petugas');
            $table->string('wa_message_id')->nullable()->unique();
            $table->string('wa_number');
            $table->string('direction', 20);
            $table->string('message_type', 30);
            $table->text('body')->nullable();
            $table->timestamp('message_at')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index(['petugas_id', 'message_at']);
            $table->index(['wa_number', 'message_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
