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
        Schema::table('fine_report', function (Blueprint $table) {
            // Fügt die Spalte 'remark' hinzu, falls sie noch nicht existiert
            if (!Schema::hasColumn('fine_report', 'remark')) {
                $table->string('remark')->nullable()->after('fine_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fine_report', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }
};