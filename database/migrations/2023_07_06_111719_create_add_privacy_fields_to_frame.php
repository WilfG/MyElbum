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
        Schema::table('frames', function (Blueprint $table) {
            $table->boolean('shareability')->default(false);
            $table->integer('shareability_code');
            $table->enum('visibility', ['Everyone', 'MyContacts', 'MyContacts_Except', 'Nobody'])->default('Everyone');
            $table->string('visibility_except_ids')->nullable();
            $table->enum('canCommentReact', ['Everyone', 'MyContacts', 'MyContacts_Except', 'Nobody'])->default('Everyone');
            $table->string('canCommentReact_except_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('add_privacy_fields_to_frame');
    }
};
