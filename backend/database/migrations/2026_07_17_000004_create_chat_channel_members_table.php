<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_channel_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_channel_id');
            $table->foreign('chat_channel_id')->references('id')->on('chat_channels')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('visible_from')->nullable();
            $table->timestamps();

            $table->unique(['chat_channel_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_channel_members');
    }
};
