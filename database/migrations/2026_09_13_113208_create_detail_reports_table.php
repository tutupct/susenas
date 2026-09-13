<?php

use App\Models\DetailReport;
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
        Schema::create('detail_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained();
            $table->text('keterangan_error');
            $table->string('foto')->nullable();
            $table->string('status')->default(DetailReport::STATUS_DRAFT);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_reports');
    }
};
