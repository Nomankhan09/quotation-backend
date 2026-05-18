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
        Schema::connection('tenant')->create('deals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('stage_id');
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('title');
            $table->decimal('value', 15, 2)->default(0);
            $table->date('expected_close_date')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
            // Foreign Keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('stage_id')
                ->references('id')
                ->on('deal_stages')
                ->onDelete('cascade');

            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');

            $table->foreign('assigned_to')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes
            $table->index('user_id');
            $table->index('stage_id');
            $table->index('lead_id');
            $table->index('assigned_to');
            $table->index('expected_close_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('deals');
    }
};
