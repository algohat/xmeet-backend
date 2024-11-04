<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{

    public function run(): void
    {
        $packages = [
            [
                'name' => 'Unbegrenzt',
                'type' => 'Monthly',
                'duration' => 30,
                'price' => 6,
            ],
             [
                'name' => 'Unbegrenzt',
                'type' => 'Yearly',
                'duration' => 365,
                'price' => 48,
            ],

             [
                'name' => 'Premium',
                'type' => 'Monthly',
                'duration' => 30,
                'price' => 12,
            ],

             [
                'name' => 'Premium',
                'type' => 'Yearly',
                'duration' => 365,
                'price' => 100,
            ],

        ];

        foreach ($packages as $package) {
            Package::Create($package);
        }
    }
}
