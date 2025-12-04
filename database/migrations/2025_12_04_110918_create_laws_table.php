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
        Schema::create('laws', function (Blueprint $table) {
            $table->id();
            $table->string('book'); // z.B. "StGB", "StVO"
            $table->string('paragraph'); // z.B. "§ 223"
            $table->string('title'); // z.B. "Körperverletzung"
            $table->text('content'); // Der eigentliche Gesetzestext
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laws');
    }
};
