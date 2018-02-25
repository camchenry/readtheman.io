<?php
declare(strict_types=1);

use Illuminate\Database\Seeder;

class SectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sections = [
            ['section' => '1', 'description' => 'User commands'],
            ['section' => '2', 'description' => 'System calls'],
            ['section' => '3', 'description' => 'Library functions'],
            ['section' => '4', 'description' => 'Special files'],
            ['section' => '5', 'description' => 'File formats'],
            ['section' => '6', 'description' => 'Games'],
            ['section' => '7', 'description' => 'Miscellaneous'],
            ['section' => '8', 'description' => 'System administration'],
        ];

        foreach($sections as $record) {
            DB::table('sections')->insert($record);
        }
    }
}
