<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correo_comercial', function (Blueprint $table) {
            $table->id();
            $table->string('idUser');
            $table->string('proveedor');
            $table->string('email_comercial');
            $table->string('clave');
            $table->timestamps();

            $table->foreign('idUser')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correo_comercial');
    }
};
