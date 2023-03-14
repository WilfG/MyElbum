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
        Schema::create('frame_bins', function (Blueprint $table) {
            $table->id();
            $table->index('frame_id');
            $table->foreignId('frame_id')->references('id')->on('frames')->onDelete('cascade');
            $table->index('frame_content_id');
            $table->foreignId('frame_content_id')->references('id')->on('frame_contents')->onDelete('cascade');
            $table->timestamp('delete_date');
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
        Schema::dropIfExists('frame_bins');
    }
};
