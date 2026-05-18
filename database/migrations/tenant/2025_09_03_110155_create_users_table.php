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
        Schema::connection('tenant')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            // $table->string('company_name')->nullable();
            // $table->text('company_address')->nullable();
            // $table->string('zip_code')->nullable();
            // $table->string('company_phone')->nullable();
            // $table->string('website')->nullable();
            // $table->string('company_type')->nullable();
            // $table->string('company_logo')->nullable();
            // $table->string('pdf_file_name_format')->default('Quotation_{date}');
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('users');
    }
};
