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
        Schema::create('user_contacts', function (Blueprint $table) {
            $table->id();
            $table->enum('request_status', ['Pending', 'Confirm', 'Reject'])->default('Pending');
            $table->enum('request_notification', ['No', 'Yes'])->default('Yes');
            $table->index('user_id');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('user_contacts');
    }
};
