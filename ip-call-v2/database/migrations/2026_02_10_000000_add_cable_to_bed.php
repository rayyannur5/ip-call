<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bed', function (Blueprint $table) {
            $table->boolean('cable')->default(0)->after('bypass');
        });
    }

    public function down(): void
    {
        Schema::table('bed', function (Blueprint $table) {
            $table->dropColumn('cable');
        });
    }
};
