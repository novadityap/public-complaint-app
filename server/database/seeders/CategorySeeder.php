<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $categrories= [
        'Religious Concerns',
        'Education System',
        'Healthcare Services',
        'Environmental Problems',
        'Public Infrastructure',
        'Government Services',
        'Social Welfare',
        'Digital Services',
        'Governance & Politics',
        'Miscellaneous Issues'
      ];

      foreach ($categrories as $category) {
        Category::create(['name'=> $category]);
      }

    }
}
