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
    Schema::table('orders', function (Blueprint $table) {
        $table->json('shipping_address')->nullable()->after('status');
        $table->enum('payment_method', ['card', 'cash'])->default('card')->after('shipping_address');
        $table->unsignedInteger('discount_miles')->default(0)->after('payment_method');
        $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_miles');
        $table->decimal('final_total', 10, 2)->nullable()->after('discount_amount');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn([
            'shipping_address',
            'payment_method',
            'discount_miles',
            'discount_amount',
            'final_total',
        ]);
    });
}
};
