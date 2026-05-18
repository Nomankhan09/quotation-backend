<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::connection('tenant')->create('contact_status', function (Blueprint $table) {
            $table->id();
            $table->string('status')->unique();
            $table->string('color')->nullable();
            $table->timestamps();
            // Indexes
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('contact_status');
    }
};
