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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('stripe_price_id',200)->nullable();
            $table->string('plan_name',200);
            $table->double('plan_price',10.2)->nullable();
            $table->tinyInteger('plan_type')->comment('1 for monthly, 2 yearly')->default(1);
            $table->tinyInteger('status')->comment('0 for inactive, 1 for active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
