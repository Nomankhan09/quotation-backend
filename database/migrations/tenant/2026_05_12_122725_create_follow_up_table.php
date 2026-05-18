<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('tenant')->create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('contact_id');
            $table->string('title');
            $table->string('type');
            $table->dateTime('date');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->string('notification_id')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('contact_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('contact_id');
            $table->index('type');
            $table->index('status');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('follow_ups');
    }
};
