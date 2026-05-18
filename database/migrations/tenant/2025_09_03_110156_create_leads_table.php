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
        Schema::connection('tenant')->create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('full_name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->string('location')->nullable();
            $table->string('source')->nullable();
            $table->string('stage')->default('new');
            $table->string('profile_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign Key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            // Indexes
            $table->index('user_id');
            $table->index('email');
            $table->index('phone');
            $table->index('stage');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('leads');
    }
};
