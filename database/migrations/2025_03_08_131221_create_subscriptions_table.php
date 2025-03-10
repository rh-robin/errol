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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('user_id'); // Reference to users table
            $table->string('stripe_subscription_id'); // Stripe subscription ID
            $table->string('stripe_customer_id'); // Stripe customer ID
            $table->string('plan_id'); // reference to plans table
            $table->timestamp('trial_ends_at')->nullable(); // Trial period end date
            $table->timestamp('start_at')->nullable(); // Subscription start date
            $table->timestamp('end_at')->nullable(); // Subscription end date
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
