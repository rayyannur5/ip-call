<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oximonitor_status', function (Blueprint $table) {
            $table->id();
            $table->float('flow_rate');
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('oximonitor_log', function (Blueprint $table) {
            $table->id();
            $table->float('volume');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oximonitor_log');
        Schema::dropIfExists('oximonitor_status');
    }
};
