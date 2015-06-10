<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

        $this->call('ContactsTableSeeder');
        $this->call('AddressTableSeeder');
        $this->call('EmailTableSeeder');
        $this->call('PhoneTableSeeder');

	}

}