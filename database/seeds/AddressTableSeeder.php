<?php

use Illuminate\Database\Seeder;

class AddressTableSeeder extends Seeder {

    public function run()
    {
        DB::table('addresses')->truncate();

        $data = [
            [
                'contact_id' => 1,
                'address_1'  => '123 Main Street',
                'address_2'  => 'Apt. 3G',
                'city'       => 'Pawnee',
                'state'      => 'Indiana',
                'zip'        => '46205',
                'country'    => 'United States'
            ],
            [
                'contact_id' => 2,
                'address_1'  => '456 Main Street',
                'address_2'  => '',
                'city'       => 'Pawnee',
                'state'      => 'Indiana',
                'zip'        => '46205',
                'country'    => 'United States'
            ],
        ];

        DB::table('addresses')->insert($data);
    }

}