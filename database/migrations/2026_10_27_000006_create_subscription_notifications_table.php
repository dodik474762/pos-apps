<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('type');
            $table->date('sent_on');
            $table->string('channel');
            $table->string('recipient')->nullable();
            $table->string('status')->default('SENT');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'type', 'sent_on', 'channel'], 'uq_subscription_notification');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_notifications');
    }
};
