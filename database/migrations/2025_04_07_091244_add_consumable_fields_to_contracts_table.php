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
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('consumable')->default(false)->after('recurring');
            $table->integer('amount')->nullable()->after('consumable');
            $table->integer('consumed_amount')->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('consumable');
            $table->dropColumn('amount');
            $table->dropColumn('consumed_amount');
        });
    }
};
