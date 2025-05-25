<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Tax;

class TaxesSeeder extends Seeder
{
    public function run()
    {
        $country_id = Country::where('name','India')->where('country_id',101)->pluck('id')->first();
        $taxes = [
            // India GST Rates for Agriculture Products
            [
                'country_id' => $country_id,
                'tax_name' => 'GST 0%',
                'tax_percentage' => 0.0,
                'description' => 'Unprocessed food items like fresh fruits, vegetables, unbranded cereals, and pulses',
            ],
            [
                'country_id' => $country_id,
                'tax_name' => 'GST 5%',
                'tax_percentage' => 5.0,
                'description' => 'Branded cereals, edible oils, tea, coffee, frozen vegetables, and fertilizers',
            ],
            [
                'country_id' => $country_id,
                'tax_name' => 'GST 12%',
                'tax_percentage' => 12.0,
                'description' => 'Packaged and branded agricultural goods, processed food items, organic fertilizers',
            ],
            [
                'country_id' => $country_id,
                'tax_name' => 'GST 18%',
                'tax_percentage' => 18.0,
                'description' => 'Instant food mixes, machinery for agriculture',
            ],
            // Other Countries' Tax Rates
            // [
            //     'country' => 'United States',
            //     'tax_name' => 'Sales Tax',
            //     'tax_percentage' => 10.0, // Example Sales Tax percentage
            //     'description' => 'General sales tax in the United States',
            // ],
            // [
            //     'country' => 'United Kingdom',
            //     'tax_name' => 'VAT',
            //     'tax_percentage' => 20.0, // Example VAT percentage
            //     'description' => 'Value-added tax in the United Kingdom',
            // ],
            // [
            //     'country' => 'Canada',
            //     'tax_name' => 'HST',
            //     'tax_percentage' => 13.0, // Example HST percentage
            //     'description' => 'Harmonized sales tax in Canada',
            // ],
        ];

        foreach ($taxes as $tax) {
            Tax::updateOrCreate($tax);
        }
    }
}
