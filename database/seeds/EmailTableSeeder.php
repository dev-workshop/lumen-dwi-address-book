<?php

use Illuminate\Database\Seeder;

class EmailTableSeeder extends Seeder {

    public function run()
    {
        DB::table('emails')->truncate();

        $data = [
            [
                'contact_id' => 1,
                'email'      => 'ron.swanson@example.com'
            ],
            [
                'contact_id' => 2,
                'email'      => 'knope@example.com'
            ],
        ];

        DB::table('emails')->insert($data);
    }

}