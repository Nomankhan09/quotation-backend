<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('tenant')->create('deal_stages', function (Blueprint $table) {
            $table->id();
            $table->string('stage_name')->unique();
            $table->integer('probability')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_won')->default(false);
            $table->string('color')->nullable();
            $table->timestamps();
            // Indexes
            $table->index('stage_name');
            $table->index('probability');
            $table->index('is_closed');
            $table->index('is_won');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('deal_stages');
    }
};
