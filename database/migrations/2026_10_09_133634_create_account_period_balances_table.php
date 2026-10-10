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
        Schema::create('account_period_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('accounting_period_id');
            $table->decimal('opening_balance', 18, 2)->default(0.00);
            $table->decimal('total_debit', 18, 2)->default(0.00);
            $table->decimal('total_credit', 18, 2)->default(0.00);
            $table->decimal('closing_balance', 18, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['account_id', 'accounting_period_id'], 'account_period_balances_account_period_unique');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('accounting_period_id')->references('id')->on('accounting_periods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_period_balances');
    }
};
