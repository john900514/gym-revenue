<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveReportsByDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_reports_by_days', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_id')->index();
            $table->string('gr_location_id');
            $table->date('date');
            $table->string('action');
            $table->string('entity');
            $table->float('value')->default(0);
            $table->timestamps();

            //$table->unique(['client_id', 'gr_location_id', 'date', 'action', 'entity'], 'unique_by_day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('live_reports_by_days');
    }
}
