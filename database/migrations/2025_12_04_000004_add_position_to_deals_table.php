<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionToDealsTable extends Migration
{
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('stage_id');
        });
    }

    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
}
