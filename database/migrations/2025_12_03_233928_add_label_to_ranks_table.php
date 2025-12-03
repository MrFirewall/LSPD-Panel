<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ranks', function (Blueprint $table) {
            // Fügt 'label' nach 'name' ein
            $table->string('label')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('ranks', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};