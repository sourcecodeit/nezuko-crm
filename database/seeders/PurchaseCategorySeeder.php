<?php

namespace Database\Seeders;

use App\Models\PurchaseCategory;
use Illuminate\Database\Seeder;

class PurchaseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'External collaborators / Freelancers',
            'Employee welfare / Fringe benefits',
            'Accounting and tax consulting',
            'Software services / Accounting software',
            'Photography / Media / Communication services',
            'AI services / APIs',
            'Vehicle purchase / Maintenance',
            'Office maintenance / Craft services',
            'Cloud / Hosting / Infrastructure services',
            'Cloud / Hosting / Dedicated servers',
            'Vehicle rental / Transportation',
            'Domains / Hosting',
            'Cleaning services / Office services',
            'Equipment / IT and office supplies',
            'Digital signature / Certified services',
            'Restaurants / Representation expenses',
            'Software / Licenses',
            'APIs / IT services',
            'Transportation / Tolls',
            'Software services (AI coding assistant)',
            'Telephony / Internet connection',
            'Hardware / Equipment',
            'Software / Digital services / Subscriptions',
            'Managed hosting / Cloud services',
            'Other',
        ];

        foreach ($categories as $category) {
            PurchaseCategory::create(['name' => $category]);
        }
    }
}
