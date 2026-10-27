<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('number');
            $table->decimal('amount', 18, 2)->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');
            $table->enum('status', ['PENDING', 'PAID', 'EXPIRED', 'FAILED', 'VOID'])->default('PENDING');
            $table->string('gateway')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->text('payment_url')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('number', 'uq_sub_inv_number');
            $table->unique('gateway_reference', 'uq_sub_inv_gateway_ref');
            $table->index('company_id');
            $table->index('subscription_id');
            $table->index('status');
            $table->index(['subscription_id', 'period_start', 'period_end'], 'idx_sub_inv_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
