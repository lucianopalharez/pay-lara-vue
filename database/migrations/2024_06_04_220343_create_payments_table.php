<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('billingId', 250)->nullable();
            $table->integer('user_id')->index();
            $table->string('invoiceNumber', 250);
            $table->text('bankSlipUrl')->nullable();
            $table->text('invoiceUrl')->nullable();
            $table->string('externalReference', 250)->nullable();
            $table->longText('description')->nullable();
            $table->longText('encodedImage')->nullable();  
            $table->longText('payload')->nullable();           
            $table->string('status', 150)->nullable();
            $table->string('pixTransaction', 250)->nullable();
            $table->string('canBePaidAfterDueDate', 50)->nullable();
            $table->string('billingType', 50)->nullable();
            $table->decimal('value', 8, 2);            
            $table->timestamp('expirationDate')->nullable();
            $table->timestamp('dueDate')->nullable();
            $table->timestamp('paymentCreated')->nullable();                      
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
