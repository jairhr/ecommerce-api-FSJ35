<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('stripe_payment_intent_id')
                ->unique();

            $table->decimal('amount', 10, 2);

            $table->string('currency', 3)->default('usd');

            $table->enum('status', [
                'pending',
                'succeeded',
                'failed',
                'cancelled',
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};