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
        Schema::create('adzan', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->time('value')->nullable();
        });

        Schema::create('room', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('name');
            $table->string('running_text')->nullable();
            $table->string('type_bed')->nullable();
            $table->string('bed_separator')->nullable();
            $table->string('serial_number')->nullable();
            $table->integer('bypass')->default(0);
        });

        Schema::create('bed', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('room_id');
            $table->string('username');
            $table->integer('vol')->default(100);
            $table->integer('mic')->default(100);
            $table->integer('tw')->default(1);
            $table->integer('mode')->default(0);
            $table->string('ip')->nullable();
            $table->string('serial_number')->nullable();
            $table->integer('bypass')->default(0);
            $table->string('phone', 6);
            
            // $table->foreign('room_id')->references('id')->on('room'); // Optional: Add FK constraint
        });

        Schema::create('toilet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('room_id');
            $table->string('username');
            $table->string('serial_number')->nullable();
            $table->integer('bypass')->default(0);
        });

        Schema::create('category_history', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('category_log', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('history', function (Blueprint $table) {
            $table->id();
            $table->string('bed_id');
            $table->integer('category_history_id');
            $table->string('duration')->nullable();
            $table->string('record')->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });

        Schema::create('list_hour_audio', function (Blueprint $table) {
            $table->time('time');
            $table->integer('vol');
            // Assuming time is unique or we want no primary key? 
            // Best to have a composite or surrogate, but sticking to schema.
        });

        Schema::create('log', function (Blueprint $table) {
            $table->id();
            $table->integer('category_log_id')->unsigned();
            $table->text('value')->nullable();
            $table->string('device_id')->nullable();
            $table->bigInteger('time')->nullable();
            $table->boolean('nurse_presence')->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });

        Schema::create('mastersound', function (Blueprint $table) {
            $table->id(); 
            $table->string('name')->unique();
            $table->string('source')->nullable();
        });

        Schema::create('playlist', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('volume');
            $table->time('start_time');
            $table->time('end_time');
        });

        Schema::create('playlist_item', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('ord');
            $table->string('path')->nullable();
            $table->primary(['id', 'ord']);
        });

        Schema::create('running_text', function (Blueprint $table) {
            $table->string('topic')->primary();
            $table->integer('speed')->nullable();
            $table->integer('brightness')->nullable();
            $table->string('serial_number')->nullable();
        });

        Schema::create('users', function (Blueprint $table) { // Laravels default is users, legacy is 'user'.
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('role');
            // $table->timestamps(); // Legacy didn't have created_at/updated_at
        });

        Schema::create('utils', function (Blueprint $table) {
            $table->string('type')->primary(); // Making type Primary Key
            $table->double('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utils');
        Schema::dropIfExists('users');
        Schema::dropIfExists('running_text');
        Schema::dropIfExists('playlist_item');
        Schema::dropIfExists('playlist');
        Schema::dropIfExists('mastersound');
        Schema::dropIfExists('log');
        Schema::dropIfExists('list_hour_audio');
        Schema::dropIfExists('history');
        Schema::dropIfExists('category_log');
        Schema::dropIfExists('category_history');
        Schema::dropIfExists('toilet');
        Schema::dropIfExists('bed');
        Schema::dropIfExists('room');
        Schema::dropIfExists('adzan');
    }
};
