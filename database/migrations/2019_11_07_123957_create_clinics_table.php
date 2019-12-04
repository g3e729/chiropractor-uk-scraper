<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClinicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clinic_name')->nullable();
            $table->string('email')->nullable();
            $table->string('meters')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('fax_number')->nullable();
            $table->string('website')->nullable();
            $table->json('chiros');
            $table->string('postcode');
            $table->decimal('latitude');
            $table->decimal('longitude');
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
        Schema::dropIfExists('clinics');
    }
}
