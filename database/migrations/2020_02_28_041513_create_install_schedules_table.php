<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstallSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('install_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('schedule_name');
            $table->string('description')->nullable();
            $table->integer('project_id')->unsigned()->nullable();
            $table->integer('designer_color_id')->unsigned()->nullable();
            $table->dateTime('start_date_time')->nullable();
            $table->dateTime('end_date_time')->nullable();
            $table->boolean('all_day')->default(0);
            $table->enum('status', ['incomplete', 'complete'])->default('incomplete');
            $table->enum('repeat', ['yes', 'no'])->default('no');
            $table->integer('repeat_every')->nullable();
            $table->integer('repeat_cycles')->nullable();
            $table->enum('repeat_type', ['day', 'week', 'month', 'year'])->default('day');
            $table->enum('send_reminder', ['yes', 'no'])->default('no');
            $table->integer('remind_time')->nullable();
            $table->enum('remind_type', ['day', 'hour', 'minute'])->default('day');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('SET NULL')->onUpdate('cascade');
            $table->foreign('designer_color_id')->references('id')->on('designer_colors')->onDelete('NO ACTION')->onUpdate('NO ACTION');
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
        Schema::dropIfExists('install_schedules');
    }
}
