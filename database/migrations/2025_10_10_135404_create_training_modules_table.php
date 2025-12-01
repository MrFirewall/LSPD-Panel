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
        Schema::create('training_modules', function (Blueprint $table) {
            $table->id();

            // Verknüpfung zum User (aus Datei 1)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Modul-Details (aus Datei 2 + 'module_name' aus Datei 1 zusammengeführt)
            $table->string('name'); // z.B. "BLS Zertifizierung"
            $table->string('category')->nullable(); // z.B. "Medizinisch", "Taktisch"
            $table->text('description')->nullable(); // Beschreibung des Inhalts

            // Durchführungsinformationen (aus Datei 1)
            $table->string('instructor_name'); // Wer hat es unterrichtet?
            $table->date('date'); // Wann fand es statt?

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_modules');
    }
};
