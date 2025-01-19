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
        Schema::create('user_package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_package_id')->constrained('user_packages')->onDelete('cascade');
            $table->foreignId('package_feature_id')->constrained('package_features')->onDelete('cascade');
            $table->string('feature_type')->nullable()->index();
            $table->integer('value')->nullable();
            $table->integer('time_limit')->nullable();
            $table->string('time_option')->nullable();
            $table->dateTime('expiration_date_time')->nullable();
            $table->text('description')->nullable();
            $table->integer('used_amount')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_package_features');
    }
};
