<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->longText('markup')->nullable();
            $table->longText('json')->nullable();
            $table->json('thumbnail')->nullable();
            $table->jsonb('details')->nullable();
            $table->boolean('active')->default(1);
            $table->uuid('client_id')->nullable()->index();
            $table->uuid('team_id')->nullable();
            $table->string('created_by_user_id');
            $table->unique(['client_id','team_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_templates');
    }
}
