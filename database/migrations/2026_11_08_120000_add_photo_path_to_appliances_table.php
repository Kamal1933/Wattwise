<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Let users attach a real photo of each appliance.
     */
    public function up(): void
    {
        Schema::table('appliances', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('appliances', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
