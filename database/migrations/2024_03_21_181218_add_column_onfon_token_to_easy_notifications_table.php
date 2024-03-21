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
        Schema::table('easy_notifications', function (Blueprint $table) {
            $table->text('onfon_token')->nullable()->change();
            $table->string('at_token')->nullable()->after('onfon_token')->comment('Africas talking auth token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('easy_notifications', function (Blueprint $table) {
            $table->dropColumn('at_token');
        });
    }
};
