<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->string('catalog_section'); // z.B. "Geschwindigkeitsüberschreitungen"
            $table->string('offense'); // Tatbestand
            $table->integer('amount'); // Geldstrafe in €
            $table->integer('jail_time')->default(0); // Hafteinheiten
            $table->integer('points')->default(0); // Punkte
            $table->string('remark')->nullable(); // Bemerkung (z.B. "Fahrverbot")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
