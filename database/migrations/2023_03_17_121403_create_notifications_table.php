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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['standard', 'viewed'])->default('standard');
            $table->enum('action', ['share', 'transfer', 'comment', 'tag', 'like', 'view']);
            $table->index('user_id');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('contact_id');
            $table->foreignId('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->string('post_id');
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
        Schema::dropIfExists('notifications');
    }
};
