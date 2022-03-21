<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('owners')->insert([
            [
                'name' => 'Kaira',
                'email' => 'takaproject777@gmail.com',
                'password' => Hash::make('pepenao0522'),
                'created_at' => '2022/03/16 11:11:11',
            ],
            [
                'name' => 'Pepe',
                'email' => 'cheap_trick_magic@yahoo.co.jp',
                'password' => Hash::make('czk68346'),
                'created_at' => '2022/03/16 11:11:11',
            ],
            [
                'name' => 'Mieko',
                'email' => 'takaki_5573031@yahoo.co.jp',
                'password' => Hash::make('ggz6kxp3'),
                'created_at' => '2022/03/16 11:11:11',
            ],
            [
                'name' => 'test1',
                'email' => 'test1@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2022/03/16 11:11:11',
            ],
            [
                'name' => 'test2',
                'email' => 'test2@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2022/03/16 11:11:11',
            ],
            [
                'name' => 'test3',
                'email' => 'test3@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2022/03/16 11:11:11',
            ],
        ]);
    }
}
