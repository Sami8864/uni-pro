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
        Schema::create('headshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('profile_progress')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('image_types')->onDelete('cascade');
            $table->string('url');
            $table->boolean('status')->nullable();
            $table->timestamps();
        });



        Schema::create('ai_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('user_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_type')->constrained('ai_attributes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('headshot')->constrained('headshots')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('disagree')->nullable();
            $table->unsignedBigInteger('agree')->nullable();
            $table->string('answer')->nullable();
            $table->string('attribute_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('headshots');
        Schema::dropIfExists('ai_attributes');
        Schema::dropIfExists('user_attributes');

    }
};
