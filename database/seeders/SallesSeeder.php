<?php

namespace Database\Seeders;

use App\Models\Salle;
use App\Support\Classes\StatesClass;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SallesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Salle::firstOrCreate([
            'nom'=>'A1',
            'status'=>StatesClass::Activated(),
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);

        Salle::firstOrCreate([
            'nom'=>'Conférence',
            'status'=>StatesClass::Activated(),
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
    }
}
