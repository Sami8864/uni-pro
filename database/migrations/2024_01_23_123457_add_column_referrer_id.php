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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('referrer_id')->nullable();
            $table->foreign('referrer_id')->references('id')->on('profile_progress')->onDelete('set null');
            $table->unsignedBigInteger('profileprogess_id')->nullable();
            $table->foreign('profileprogess_id')->references('id')->on('profile_progress')->onDelete('set null');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn('referrer_id');
            $table->dropForeign(['profileprogess_id']);
            $table->dropColumn('profileprogess_id');
        });
    }
};
