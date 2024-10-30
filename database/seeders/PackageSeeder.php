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
                'name' => 'Monthly',
                'duration' => 30, // 30 days
                'price' => 9,
            ],
            [
                'name' => 'Yearly',
                'duration' => 365, // 365 days
                'price' => 12,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(['name' => $package['name']], $package);
        }
    }
}
