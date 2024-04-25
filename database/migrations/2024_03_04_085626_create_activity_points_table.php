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
        Schema::create('activity_points', function (Blueprint $table) {
            $table->id();
            $table->integer('upperlimit');
            $table->integer('lowerlimit');
            $table->integer('intervalsize');
            $table->integer('perintervalcontact');
            $table->integer('perintervalprice');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_points');
    }
};
