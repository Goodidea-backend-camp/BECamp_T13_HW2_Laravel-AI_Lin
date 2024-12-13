<?php

use App\Enums\MessageRoleType;
use App\Enums\MessageType;
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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->enum('role', array_column(MessageRoleType::cases(), 'value'));
            $table->enum('type', array_column(MessageType::cases(), 'value'));
            $table->text('content')->nullable();
            $table->text('file_path')->nullable();
            $table->timestamps();
            $table->foreignId('thread_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
