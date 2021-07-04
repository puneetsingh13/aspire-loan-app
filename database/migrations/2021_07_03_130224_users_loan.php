<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersLoan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_loan', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->decimal('loan_amount', 13, 2);
            $table->integer('loan_term');
            $table->integer('approved');
            $table->integer('closed');
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
        Schema::drop('users_loan');
    }
}
