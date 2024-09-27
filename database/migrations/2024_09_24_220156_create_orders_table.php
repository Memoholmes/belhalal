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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->index();
            $table->integer('user_id')->index();
            $table->integer('package_id');
            $table->decimal('amount', 8, 2);
            $table->string('currency_code', 100);
            $table->tinyInteger('payment_status')->default(0)->index();
            $table->boolean('after_payment_is_processing')->default(false)->index();
            $table->timestamp('after_payment_process_started_at')->nullable();
            $table->timestamp('after_payment_process_completed_at')->nullable();
            $table->boolean('after_payment_proccess_is_completed')->default(false)->index();
            $table->integer('payment_provider')->index();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
