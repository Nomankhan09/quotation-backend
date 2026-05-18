<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('tenant')->create('activity', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lead_id');

            $table->string('title');
            $table->string('type');

            $table->dateTime('date');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('lead_id');
            $table->index('type');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('activity');
    }
};
