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
        Schema::create('frame_contents', function (Blueprint $table) {
            $table->id();
            $table->enum('content_type', ['image', 'video']);
            $table->string('filepath');
            $table->enum('content_status', ['bin', 'live'])->nullable()->default('live');
            $table->string('pan')->nullable();
            $table->string('zoom')->nullable();
            $table->string('height')->nullable();
            $table->string('width')->nullable();
            $table->string('size')->nullable();
            $table->string('position')->nullable();
            $table->index('frame_id');
            $table->foreignId('frame_id')->references('id')->on('frames')->onDelete('cascade');
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
        Schema::dropIfExists('frame_contents');
    }
};
