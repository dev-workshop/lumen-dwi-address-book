<?php

use Illuminate\Database\Seeder;

class PhoneTableSeeder extends Seeder {

    public function run()
    {
        DB::table('phones')->truncate();

        $data = [
            [
                'contact_id' => 1,
                'phone'      => '317.555.9999'
            ],
            [
                'contact_id' => 1,
                'phone'      => '317.555.7777'
            ],
            [
                'contact_id' => 2,
                'phone'      => '812.555.6666'
            ],
        ];

        DB::table('phones')->insert($data);
    }

}