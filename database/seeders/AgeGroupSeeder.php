<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use Illuminate\Database\Seeder;

class AgeGroupSeeder extends Seeder
{
    /**
     * Seed the age_groups table from JSON data.
     */
    public function run(): void
    {
        $path = database_path('data/age_groups.json');
        $ageGroups = json_decode(file_get_contents($path), true);

        foreach ($ageGroups as $ageGroup) {
            AgeGroup::query()->updateOrCreate(
                ['code' => $ageGroup['code']],
                $ageGroup,
            );
        }
    }
}
