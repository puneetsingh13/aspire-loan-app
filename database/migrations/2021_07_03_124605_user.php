<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class User extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->string('name');
            $table->string('phone');
            $table->string('password',255);
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
