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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description');
            $table->integer('points_required');
            $table->enum('type', ['types', 'invites']);
            $table->string('award_image');
            $table->timestamps();
        });
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_progress_id')->constrained('profile_progress')->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained('achievements')->onUpdate('cascade')->onDelete('cascade');
            $table->float('percentage_achieved')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('user_achievements');
    }
};
