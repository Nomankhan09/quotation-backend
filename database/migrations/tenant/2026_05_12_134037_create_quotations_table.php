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
        /*
        |--------------------------------------------------------------------------
        | QUOTATIONS
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')->create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->foreignId('deal_id')
                ->nullable()
                ->constrained('deals')
                ->nullOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->enum(
                'discount_type',
                ['percent', 'fixed']
            )->default('fixed');
            $table->decimal('discount_value', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('status')->default('draft');
            $table->json('terms')->nullable();
            $table->json('specifications')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('set null');


            // Indexes
            $table->index('user_id');
            $table->index('lead_id');
            $table->index('deal_id');
            $table->index('status');
            $table->index('created_at');
        });

        /*
        |--------------------------------------------------------------------------
        | QUOTATION PRODUCTS
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')->create('quotation_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 14, 2)->default(0);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('quotation_id')
                ->references('id')
                ->on('quotations')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null');

            // Indexes
            $table->index('quotation_id');
            $table->index('product_id');
        });

        /*
        |--------------------------------------------------------------------------
        | QUOTATION PAYMENT TERMS
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')->create('quotation_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->string('description');
            $table->text('value');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('quotation_id')
                ->references('id')
                ->on('quotations')
                ->onDelete('cascade');

            $table->foreign('payment_term_id')
                ->references('id')
                ->on('payment_terms')
                ->onDelete('set null');

            // Indexes
            $table->index('quotation_id');
            $table->index('payment_term_id');
        });

        /*
        |--------------------------------------------------------------------------
        | QUOTATION STATUS
        |--------------------------------------------------------------------------
        */

        Schema::connection('tenant')->create('quotation_status', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->timestamps();
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('quotation_status');
        Schema::connection('tenant')->dropIfExists('quotation_payment_terms');
        Schema::connection('tenant')->dropIfExists('quotation_products');
        Schema::connection('tenant')->dropIfExists('quotations');
    }
};
