<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContactsTableSeeder extends Seeder {

    public function run()
    {
        $date = Carbon::now();

        DB::table('contacts')->truncate();

        $data = [
            [
                'first_name'     => 'Ron',
                'last_name'      => 'Swanson',
                'birthday_month' => '',
                'birthday_day'   => '',
                'birthday_year'  => '',
                'created_at'     => $date,
                'updated_at'     => $date,
            ],
            [
                'first_name'     => 'Leslie',
                'last_name'      => 'Knope',
                'birthday_month' => '01',
                'birthday_day'   => '18',
                'birthday_year'  => '1975',
                'created_at'     => $date,
                'updated_at'     => $date,
            ],
        ];

        DB::table('contacts')->insert($data);
    }

}