<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimpleTestCaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'type' => 'pf',
                'document' => '79585907682',
            ],
            [
                'type' => 'pf',
                'document' => '84359319193',
            ],
            [
                'type' => 'pj',
                'document' => '78691945000194',
            ],
            [
                'type' => 'pj',
                'document' => '29425881000174',
            ],
        ];

        for ($i = 0; $i < 4; $i++) {
            $userId = DB::table('users')->insertGetId([
                'name' => Str::random(10),
                'email' => Str::random(10) . '@gmail.com',
                'document' => $data[$i]['document'],
                'type' => $data[$i]['type'],
                'password' => Hash::make('password'),
            ]);

            DB::table('bank_accounts')->insert([
                'user_id' => $userId,
                'current_account_balance' => 500.00
            ]);
        }

    }

}
