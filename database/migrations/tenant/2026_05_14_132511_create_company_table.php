<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('company', function (Blueprint $table) {

            $table->id();
            $table->string('company_name');
            $table->text('company_address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('company_type')->nullable();
            $table->string('company_email')->nullable();
            $table->string('pdf_file_name_format')->default('Quotation_{date}');
            $table->string('gst_number')->nullable();

            // bank details
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('swift_code')->nullable();
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('company_name');
            $table->index('company_phone');
            $table->index('company_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('company');
    }
};
