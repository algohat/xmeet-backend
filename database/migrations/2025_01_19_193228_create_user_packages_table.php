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
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('package_name')->nullable();
            $table->string('package_type')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('status')->nullable()->comment('1=pending;2=active;3=expired;4=cancelled;5=inactive');
            $table->string('payment_medium')->nullable();
            $table->integer('payment_status')->nullable()->comment('1=unpaid;2=paid;3=pending;4=cancelled;5=refund');
            $table->string('order_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('user_original_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_phone')->nullable();
            $table->string('user_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_packages');
    }
};
