<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        DB::table('categories')->insert([
            'category' => "Dairy & Eggs"
        ]);

        DB::table('categories')->insert([
            'category' => "Fruits & Vegetables"
        ]);

        DB::table('categories')->insert([
            'category' => "Fish"
        ]);

        DB::table('categories')->insert([
            'category' => "Meats"
        ]);

        DB::table('categories')->insert([
            'category' => "Staple Food (Bigas)"
        ]);

        DB::table('categories')->insert([
            'category' => "Bread And Pastries"
        ]);

        DB::table('categories')->insert([
            'category' => "Frozen Food"
        ]);

        DB::table('categories')->insert([
            'category' => "Canned Goods"
        ]);

        DB::table('categories')->insert([
            'category' => "Personal Care & Cleaning Must-Haves"
        ]);

        DB::table('categories')->insert([
            'category' => "Household"
        ]);

        // positions
        DB::table('positions')->insert([
            'name' => 'Client',
            'created_at' => DB::raw("NOW()")
        ]);

        DB::table('positions')->insert([
            'name' => 'Seller',
            'created_at' => DB::raw("NOW()")
        ]);

        DB::table('positions')->insert([
            'name' => 'Rider',
            'created_at' => DB::raw("NOW()")
        ]);

        DB::table('positions')->insert([
            'name' => 'Admin',
            'created_at' => DB::raw("NOW()")
        ]);
    }
}
