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
        Schema::create('frame_content_comments', function (Blueprint $table) {
            $table->id();
            $table->string('content_comment');
            $table->index('frame_content_id');
            $table->foreignId('frame_content_id')->references('id')->on('frame_contents')->onDelete('cascade');
            $table->index('contact_id');
            $table->foreignId('contact_id')->references('id')->on('contacts')->onDelete('cascade');
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
        Schema::dropIfExists('frame_content_comments');
    }
};
