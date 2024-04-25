<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->references('id')->on('users')->onDelete('no action');
            $table->foreignId('receiver_id')->references('id')->on('users')->onDelete('no action');
            $table->boolean('blocked')->default(0);
            
            $table->timestamp('last_message_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->longText('message');
            $table->foreignId('conversation_id')->references('id')->on('conversations')->onDelete('no action');
            $table->foreignId('sender_id')->references('id')->on('users')->onDelete('no action');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sender_deleted_at')->nullable();
            $table->timestamp('receiver_deleted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
