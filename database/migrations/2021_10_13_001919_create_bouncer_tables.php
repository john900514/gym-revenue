<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Silber\Bouncer\Database\Models;

class CreateBouncerTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Models::table('abilities'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('title')->nullable();
            $table->bigInteger('entity_id')->unsigned()->nullable()->index();
            $table->string('entity_type')->nullable();
            $table->boolean('only_owned')->default(false);
            $table->json('options')->nullable();
            $table->uuid('scope')->nullable()->index();
            $table->timestamps();
        });

        Schema::create(Models::table('roles'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('title')->nullable();
            $table->integer('level')->unsigned()->nullable();
            $table->uuid('scope')->nullable()->index();
            $table->integer('group')->unsigned()->index();
            $table->timestamps();

            $table->unique(
                ['name', 'scope'],
                'roles_name_unique'
            );
        });

        Schema::create(Models::table('assigned_roles'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('role_id')->unsigned();
            $table->uuid('entity_id');
            $table->string('entity_type');
            $table->uuid('restricted_to_id')->nullable();
            $table->string('restricted_to_type')->nullable();
            $table->uuid('scope')->nullable()->index();

            $table->index(
                [
                    'role_id',
                    'entity_id',
                    'entity_type',
                    'scope',
                    'restricted_to_id',
                    'restricted_to_type',
                ],
                'assigned_roles_entity_index'
            );

            $table->foreign('role_id')

                ->references('id')->on(Models::table('roles'))
                ->onUpdate('cascade')->onDelete('cascade');
            $table->index('role_id');
        });

        Schema::create(Models::table('permissions'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ability_id')->unsigned()->index();
            $table->uuid('entity_id')->nullable();
            $table->string('entity_type')->nullable();
            $table->boolean('forbidden')->default(false);
            $table->uuid('scope')->nullable()->index();

            $table->index(
                ['entity_id', 'entity_type', 'scope'],
                'permissions_entity_index'
            );

            $table->foreign('ability_id')
                  ->references('id')->on(Models::table('abilities'))
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(Models::table('permissions'));
        Schema::drop(Models::table('assigned_roles'));
        Schema::drop(Models::table('roles'));
        Schema::drop(Models::table('abilities'));
    }
}
