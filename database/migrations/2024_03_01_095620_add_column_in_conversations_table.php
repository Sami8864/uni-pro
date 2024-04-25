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
        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('sender_muted')->default(0); // Added sender_muted column
            $table->boolean('receiver_muted')->default(0); // Added receiver_muted column
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('receiver_muted')->default(0); // Added receiver_muted column
            $table->dropColumn('sender_muted')->default(0); // Added receiver_muted column
        });
    }
};
