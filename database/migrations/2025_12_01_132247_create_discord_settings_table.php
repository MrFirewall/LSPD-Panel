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
        Schema::create('discord_settings', function (Blueprint $table) {
            $table->id();
            $table->string('action')->unique(); // Interner Key: 'rank.promotion'
            $table->string('friendly_name');    // Für das Panel: 'Benutzer wurde befördert'
            $table->string('webhook_url')->nullable(); // Hier trägt der Admin die URL ein
            $table->boolean('active')->default(false); // Standardmäßig aus, bis URL da ist
            $table->text('description')->nullable(); // Optional: Erklärungstext
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discord_settings');
    }
};
