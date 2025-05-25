<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class categorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $list = [
            // Home Appliance Repairs
            [
                'name' => 'Home Appliance Repair',
                'category_type' => 'service',
                'category_url' => 'https://res.cloudinary.com/demo/image/upload/appliance_repair.jpg',
                'sub_category' => [
                    ['name' => 'Washing Machine', 'sub_category_type' => 'repair'],
                    ['name' => 'Refrigerator', 'sub_category_type' => 'repair'],
                    ['name' => 'Microwave Oven', 'sub_category_type' => 'repair'],
                    ['name' => 'Air Conditioner', 'sub_category_type' => 'repair'],
                    ['name' => 'Water Purifier', 'sub_category_type' => 'repair'],
                    ['name' => 'Dishwasher', 'sub_category_type' => 'repair'],
                    ['name' => 'TV & Display', 'sub_category_type' => 'repair'],
                ]
            ],

            // Home Utility Services
            [
                'name' => 'Home Services',
                'category_type' => 'service',
                'category_url' => 'https://res.cloudinary.com/demo/image/upload/home_services.jpg',
                'sub_category' => [
                    ['name' => 'Plumber', 'sub_category_type' => 'repair'],
                    ['name' => 'Electrician', 'sub_category_type' => 'repair'],
                    ['name' => 'Carpenter', 'sub_category_type' => 'repair'],
                    ['name' => 'Painter', 'sub_category_type' => 'repair'],
                    ['name' => 'Pest Control', 'sub_category_type' => 'repair'],
                    ['name' => 'AC Servicing', 'sub_category_type' => 'repair'],
                    ['name' => 'Geyser Repair', 'sub_category_type' => 'repair'],
                ]
            ],

            // Second-hand Product Sales
            [
                'name' => 'Product Sales',
                'category_type' => 'product',
                'category_url' => 'https://res.cloudinary.com/demo/image/upload/product_sales.jpg',
                'sub_category' => [
                    ['name' => 'Second-Hand Electronics', 'sub_category_type' => 'sale'],
                    ['name' => 'Used Furniture', 'sub_category_type' => 'sale'],
                    ['name' => 'Kitchen Appliances', 'sub_category_type' => 'sale'],
                    ['name' => 'Mobile Phones', 'sub_category_type' => 'sale'],
                    ['name' => 'Laptop & PC', 'sub_category_type' => 'sale'],
                    ['name' => 'Fitness Equipment', 'sub_category_type' => 'sale'],
                    ['name' => 'Home Decor', 'sub_category_type' => 'sale'],
                ]
            ],

            // Personal Services
            [
                'name' => 'Personal Services',
                'category_type' => 'service',
                'category_url' => 'https://res.cloudinary.com/demo/image/upload/personal_services.jpg',
                'sub_category' => [
                    ['name' => 'Beauty & Salon', 'sub_category_type' => 'repair'],
                    ['name' => 'Massage & Spa', 'sub_category_type' => 'repair'],
                    ['name' => 'Home Nursing', 'sub_category_type' => 'repair'],
                    ['name' => 'Elder Care', 'sub_category_type' => 'repair'],
                ]
            ],
        ];

        
        foreach ($list as $category) {
            $info = Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'name' => $category['name'],
                    'category_type' => $category['category_type'],
                    'category_url' => $category['category_url'],
                ]
            );

            foreach ($category['sub_category'] as $sub_category) {
                SubCategory::updateOrCreate(
                    [
                        'name' => $sub_category['name'],
                        'category_id' => $info->id,
                    ],
                    [
                        'category_id' => $info->id,
                        'name' => $sub_category['name'],
                        'sub_category_type' => $sub_category['sub_category_type'],
                    ]
                );
            }
        }

        # create Unit 
        $units = [
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Liter', 'short_name' => 'ltr'],
            ['name' => 'Milliliter', 'short_name' => 'ml'],
            ['name' => 'Pound', 'short_name' => 'lb'],
            ['name' => 'Ounce', 'short_name' => 'oz'],
            ['name' => 'Piece', 'short_name' => 'pc'],
            ['name' => 'Packet', 'short_name' => 'Pk'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
