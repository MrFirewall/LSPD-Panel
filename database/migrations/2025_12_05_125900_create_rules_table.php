<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Z.B. "§1 Allgemeine Regeln"
            $table->longText('content'); // Der HTML Text vom Editor
            $table->integer('order_index')->default(0); // Um die Reihenfolge festzulegen
            $table->unsignedBigInteger('updated_by')->nullable(); // Wer hat es zuletzt bearbeitet?
            $table->timestamps();
            
            // Optional: SoftDeletes falls man nichts aus Versehen löschen will
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('rules');
    }
};