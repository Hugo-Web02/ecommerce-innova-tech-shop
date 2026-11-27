<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->char('currency', 3)->default('MXN')->after('total');
            $table->string('stripe_checkout_session_id')->nullable()->unique()->after('currency');
            $table->string('stripe_payment_intent_id')->nullable()->index()->after('stripe_checkout_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropUnique(['stripe_checkout_session_id']);
            $table->dropColumn(['stripe_payment_intent_id', 'stripe_checkout_session_id', 'currency']);
        });
    }
};
