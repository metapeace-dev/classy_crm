<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToInstallSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('install_schedules', function (Blueprint $table) {
            $table->string('tentative_client')->after('all_day')->nullable();
            $table->string('tentative_city')->after('tentative_client')->nullable();
            $table->double('tentative_amount', 16, 2)->after('tentative_city')->default(0);
            $table->integer('type_id')->after('tentative_amount')->unsigned()->nullable();
            $table->foreign('type_id')->references('id')->on('install_schedule_types')->onDelete('SET NULL')->onUpdate('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('install_schedules', function (Blueprint $table) {
            $table->dropForeign('install_schedules_type_id_foreign');
            $table->dropColumn('type_id');
            $table->dropColumn('tentative_client');
            $table->dropColumn('tentative_city');
            $table->dropColumn('tentative_amount');
        });
    }
}
