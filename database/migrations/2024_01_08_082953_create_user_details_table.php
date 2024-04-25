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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('profile_progress')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('weight_height')->nullable();
            $table->string('physique')->nullable();
            $table->string('location')->nullable();
            $table->string('essence')->nullable();
            $table->string('type')->nullable();
            $table->string('IMDb')->nullable();
            $table->timestamps();
        });

        Schema::create('essences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });


        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_details')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('types')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('user_essences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_details')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('essence_id')->constrained('essences')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('essences');
        Schema::dropIfExists('types');
        Schema::dropIfExists('user_types');
        Schema::dropIfExists('user_essences');
    }
};
