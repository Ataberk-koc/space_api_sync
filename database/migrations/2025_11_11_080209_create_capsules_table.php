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
        Schema::create('capsules', function (Blueprint $table) {
            $table->id();
            $table->string('capsule_serial')->unique(); // API'deki ana anahtar
            $table->string('capsule_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('original_launch')->nullable();
            $table->integer('missions_count')->default(0); 
            $table->text('details')->nullable();
            $table->json('raw_data'); // Tüm JSON verisini kaydetme zorunluluğu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capsules');
    }
};
