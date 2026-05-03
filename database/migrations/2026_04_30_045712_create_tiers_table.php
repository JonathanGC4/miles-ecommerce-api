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
    Schema::create('tiers', function (Blueprint $table) {
        $table->id();
        $table->string('name');                          // Bronze, Silver, Gold, Platinum
        $table->unsignedInteger('min_miles');            // Millas mínimas para este tier
        $table->decimal('multiplier', 3, 1);             // 1.0, 1.5, 2.0, 3.0
        $table->json('benefits')->nullable();            // ["Descuentos", "Acceso VIP"]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
