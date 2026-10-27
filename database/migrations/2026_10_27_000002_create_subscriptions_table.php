<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->unique();
            $table->unsignedBigInteger('plan_id');
            $table->enum('status', ['TRIAL', 'ACTIVE', 'PAST_DUE', 'SUSPENDED', 'CANCELED'])->default('TRIAL');
            $table->enum('billing_cycle', ['MONTHLY', 'YEARLY'])->default('MONTHLY');
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('current_period_start');
            $table->dateTime('current_period_end');
            $table->dateTime('grace_ends_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->unsignedBigInteger('pending_plan_id')->nullable();
            $table->enum('pending_billing_cycle', ['MONTHLY', 'YEARLY'])->nullable();
            $table->dateTime('pending_effective_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('plan_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
