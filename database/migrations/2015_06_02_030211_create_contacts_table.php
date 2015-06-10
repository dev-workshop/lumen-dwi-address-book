<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contacts', function(Blueprint $table)
		{
			// PK
            $table->increments('id');

            // Name
            $table->string('first_name', 255);
            $table->string('last_name', 255);

            // Birthday
            $table->tinyInteger('birthday_month')->unsigned()->nullable();
            $table->tinyInteger('birthday_day')->unsigned()->nullable();
            $table->smallInteger('birthday_year')->unsigned()->nullable();

            // Created At / Updated At
			$table->timestamps();
		});

        Schema::create('phones', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('contact_id')->unsigned();
            $table->string('phone');
        });

        Schema::create('emails', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('contact_id')->unsigned();
            $table->string('email');
        });

        Schema::create('addresses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('contact_id')->unsigned();
            $table->string('address_1');
            $table->string('address_2');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('country')->default('United States');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('addresses');
		Schema::drop('emails');
        Schema::drop('phones');
        Schema::drop('contacts');
	}

}
