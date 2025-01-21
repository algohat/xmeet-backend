<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Package::truncate();

        $packages = [
            [
                'name' => 'Free',
                'type' => 'General',
                'duration' => 0,
                'price' => 0,
                'is_paid' => 0,
            ],
            [
                'name' => 'Unbegrenzt',
                'type' => 'Monthly',
                'duration' => 30,
                'price' => 6,
                'is_paid' => 1,
            ],
            [
                'name' => 'Unbegrenzt',
                'type' => 'Yearly',
                'duration' => 365,
                'price' => 48,
                'is_paid' => 1,
            ],

            [
                'name' => 'Premium',
                'type' => 'Monthly',
                'duration' => 30,
                'price' => 12,
                'is_paid' => 1,
            ],

            [
                'name' => 'Premium',
                'type' => 'Yearly',
                'duration' => 365,
                'price' => 100,
                'is_paid' => 1,
            ],

        ];

        foreach ($packages as $package) {
            Package::Create($package);
        }
    }
}
