<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('invoice_number', 50);
            $table->date('invoice_date');
            $table->string('customer_code', 100)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('payment_term', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten', 100)->nullable();
            $table->string('channel', 100)->nullable();
            $table->string('salesman_code', 50)->nullable();
            $table->string('salesman_name', 150)->nullable();
            $table->date('due_date')->nullable();
            $table->integer('umur')->nullable();
            $table->decimal('total_piutang', 18, 2)->default(0.00);
            $table->decimal('total_dibayar', 18, 2)->default(0.00);
            $table->decimal('sisa_hutang', 18, 2)->default(0.00);
            $table->integer('overdue')->nullable();
            $table->string('cluster_overdue', 50)->nullable();
            $table->text('principal_ids')->nullable();
            $table->date('closing_date');
            $table->datetime('closed_at')->nullable();
            $table->bigInteger('closed_by')->nullable();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->timestamps();

            $table->index(['company_id', 'closing_date'], 'history_invoice_company_closing_index');
            $table->index('invoice_id', 'history_invoice_invoice_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_invoice');
    }
};
