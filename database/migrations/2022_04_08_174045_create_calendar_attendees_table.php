<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarAttendeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_attendees', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->string('entity_type');
            $table->string('entity_id');
            $table->json('entity_data');
            $table->uuid('calendar_event_id');
            $table->string('invitation_status');
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
        Schema::dropIfExists('calendar_attendees');
    }
}