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
        Schema::connection('tenant')->create('app_errors', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->text('message');

            $table->longText('stack')->nullable();

            $table->string('screen')->nullable();

            $table->string('platform')->nullable();

            $table->boolean('is_fatal')->default(false);

            $table->string('app_version')->nullable();

            $table->timestamps();

            // Foreign Key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes
            $table->index('user_id');
            $table->index('platform');
            $table->index('is_fatal');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('app_errors');
    }
};
