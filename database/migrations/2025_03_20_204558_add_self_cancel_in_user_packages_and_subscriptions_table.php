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
        Schema::table('user_packages', function (Blueprint $table) {
            $table->boolean('self_cancel')->default(0)->after('status');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('self_cancel')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_packages_and_subscriptions', function (Blueprint $table) {
            //
        });
    }
};
