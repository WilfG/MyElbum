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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('notification_sounds')->nullable()->default(false);
            $table->boolean('notification_vibrate')->nullable()->default(false);
            $table->boolean('notification_contentComments_reactions')->nullable()->default(false);
            $table->boolean('notification_add_content_to_profile_album')->nullable()->default(false);
            $table->boolean('notification_new_tag_in_content')->nullable()->default(false);
            $table->boolean('notification_content_deleted')->nullable()->default(false);
            $table->index('user_id');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('settings');
    }
};
