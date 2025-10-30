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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->enum('discount_type', ['percent', 'fixed'])->default('fixed');
            $table->decimal('discount_value', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected'])->default('draft');
            $table->json('terms')->nullable();
            $table->timestamps();
        });

        Schema::create('quotation_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('quotation_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->string('description');
            $table->text('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_payment_terms');
        Schema::dropIfExists('quotation_products');
        Schema::dropIfExists('quotations');
    }
};
