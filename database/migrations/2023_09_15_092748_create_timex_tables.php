<?php

use App\Support\Classes\StatesClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table){
            $table->uuid('id');
            $table->string('value');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
        });
        
        Schema::create('evenements', function (Blueprint $table) {
            $table->uuid('id');
            $table->json('attachments')->nullable();
            $table->longText('body')->nullable();
            $table->string('category')->nullable();
            $table->date('end');
            $table->time('endTime')->nullable();
            $table->boolean('isAllDay')->default(false);
            $table->uuid('organizer');
            $table->json('participants')->nullable();
            $table->longText('subject');
            $table->date('start');
            $table->time('startTime')->nullable();
            $table->enum('statut',[
                StatesClass::Activated(),
                StatesClass::Deactivated(),
                StatesClass::Suspended(),
                ])->nullable();

            $table->unsignedBigInteger('salle_id');
            $table->foreign('salle_id')->references('id')->on('salles');


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
        Schema::dropIfExists('evenements');
        Schema::dropIfExists('categories');
    }
};
