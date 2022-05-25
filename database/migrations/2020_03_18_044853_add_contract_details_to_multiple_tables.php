<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractDetailsToMultipleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('cell_contact')->after('cell')->nullable();
            $table->string('cell2_contact')->after('cell2')->nullable();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('cell_contact')->after('cell')->nullable();
            $table->string('cell2_contact')->after('cell2')->nullable();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('cell_contact')->after('cell')->nullable();
            $table->string('cell2_contact')->after('cell2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('cell_contact');
            $table->dropColumn('cell2_contact');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('cell_contact');
            $table->dropColumn('cell2_contact');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('cell_contact');
            $table->dropColumn('cell2_contact');
        });
    }
}
