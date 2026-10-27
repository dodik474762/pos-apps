<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_webhook_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('gateway');
            $table->string('event_id')->nullable();
            $table->longText('payload');
            $table->boolean('signature_valid')->default(false);
            $table->enum('status', ['RECEIVED', 'PROCESSED', 'IGNORED', 'FAILED'])->default('RECEIVED');
            $table->text('error')->nullable();
            $table->dateTime('received_at');
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'event_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_logs');
    }
};
