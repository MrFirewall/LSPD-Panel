<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('start_time')->nullable(false);
            $table->timestamp('end_time')->nullable();          // ERLAUBT NULL für laufende Dienste
            $table->integer('duration_seconds')->nullable();     // ERLAUBT NULL für laufende Dienste
            // ----------------------------------------------------
            
            $table->string('type')->default('DUTY'); // Optional: 'DUTY', 'LEITSTELLE'
            $table->timestamps();
            $table->index(['user_id', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_records');
    }
};