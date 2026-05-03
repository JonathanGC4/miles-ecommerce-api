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
    Schema::create('miles_accounts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('tier_id')->constrained()->onDelete('restrict');
        $table->unsignedInteger('balance')->default(0);
        $table->unsignedInteger('lifetime_miles')->default(0); // Millas acumuladas total (nunca bajan)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miles_accounts');
    }
};
