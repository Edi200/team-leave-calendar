<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert order defines on-call rotation sequence: id 1 → week 0, id 2 → week 1, etc.
        foreach (['Alice', 'Bob', 'Charlie', 'Diana'] as $name) {
            TeamMember::create(['name' => $name]);
        }
    }
}
