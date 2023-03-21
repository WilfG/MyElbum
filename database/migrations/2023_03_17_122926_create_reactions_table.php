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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['like', 'view']);
            $table->index('user_id');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('contact_id');
            $table->foreignId('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->integer('frame_id')->nullable();
            $table->integer('frame_content_id')->nullable();
            $table->integer('comment_id')->nullable();
            $table->integer('content_comment_id')->nullable();
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
        Schema::dropIfExists('reactions');
    }
};
