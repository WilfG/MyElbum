<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('lastname')->nullable();
            $table->string('firstname')->nullable();
            $table->string('country')->nullable();
            $table->string('username')->nullable();
            $table->string('birthDate')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phoneNumber')->unique()->nullable();
            $table->boolean('isVerified')->default(false);
            $table->boolean('otpVerified')->default(false);
            $table->string('google_id')->nullable();
            $table->string('profil_picture')->nullable();
            $table->enum('role', ['user','admin','super_admin'])->default('user');
            $table->decimal('latitude')->nullable();
            $table->decimal('longitude')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
