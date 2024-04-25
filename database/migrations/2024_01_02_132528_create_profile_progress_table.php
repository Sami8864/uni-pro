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
        Schema::create('profile_progress', function (Blueprint $table) {
            $table->id();
            $table->integer('battery_level');
            $table->integer('account_level');
            $table->string('types_points')->default(0);
            $table->string('invites_points')->default(0);
            $table->string('device_id');
            $table->string('available_contacts')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_progress');
    }
};
