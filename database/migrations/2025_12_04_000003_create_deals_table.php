<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealsTable extends Migration
{
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('company')->nullable();
            $table->decimal('value', 12, 2)->default(0);
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('stage_id')->references('id')->on('stages')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deals');
    }
}
