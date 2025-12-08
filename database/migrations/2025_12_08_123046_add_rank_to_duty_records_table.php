<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('duty_records', function (Blueprint $table) {
            // Speichert den Rang-Slug (z.B. 'captain') zum Zeitpunkt des Dienstes
            $table->string('rank')->nullable()->after('user_id'); 
            $table->index('rank'); // Für schnellere Gruppierung im Archiv
        });
    }

    public function down(): void
    {
        Schema::table('duty_records', function (Blueprint $table) {
            $table->dropColumn('rank');
        });
    }
};