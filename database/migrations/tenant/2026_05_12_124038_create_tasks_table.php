<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | TASK PRIORITY
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')->create('task_priority', function (Blueprint $table) {
            $table->id();
            $table->string('priority')->unique();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('priority');
        });

        /*
        |--------------------------------------------------------------------------
        | TASK STATUS
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')->create('task_status', function (Blueprint $table) {
            $table->id();
            $table->string('status')->unique();
            $table->string('color')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | TASK
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')->create('task', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('priority')->nullable();
            $table->string('title');
            $table->string('status')->default('pending');
            $table->dateTime('due_date')->nullable();
            $table->longText('notes')->nullable();
            $table->string('notification_id')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('contact_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('priority')
                ->references('id')
                ->on('task_priority')
                ->onDelete('set null');

            // Indexes
            $table->index('contact_id');
            $table->index('user_id');
            $table->index('priority');
            $table->index('status');
            $table->index('due_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('task');
        Schema::connection('tenant')->dropIfExists('task_status');
        Schema::connection('tenant')->dropIfExists('task_priority');
    }
};
