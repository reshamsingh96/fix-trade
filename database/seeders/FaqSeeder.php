<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faq = [
            [
                'title' => 'What is Anaaj Culture?',
                'description' => 'Anaaj Culture is an agricultural marketplace app that helps farmers and agricultural businesses buy or sell tools and goods across various categories like fertilizers, grains, machinery, animals, and labor services.'
            ],
            [
                'title' => 'How do I get started with Anaaj Culture?',
                'description' => 'Download the app, sign up, and browse categories or use the search function to find what you need.'
            ],
            [
                'title' => 'What types of fertilizers are available?',
                'description' => 'Our platform offers a variety of fertilizers, including organic, chemical, and specialty fertilizers tailored for different crop needs.'
            ],
            [
                'title' => 'How do I know the fertilizer is genuine?',
                'description' => 'We verify sellers to ensure quality, and customer reviews help you choose trusted suppliers.'
            ],
            [
                'title' => 'Can I buy grains in bulk?',
                'description' => 'Yes, bulk buying options are available for all grain types, depending on seller listings.'
            ],
            [
                'title' => 'How do I sell grains on Anaaj Culture?',
                'description' => 'Simply register as a seller, upload details about your grains, set your price, and start selling.'
            ],
            [
                'title' => 'What types of machinery can I find on Anaaj Culture?',
                'description' => 'You can find various agricultural machinery, including tractors, harvesters, plows, and seeders. Some machinery is also available for rent.'
            ],
            [
                'title' => 'Are there warranties for machinery?',
                'description' => 'Warranties depend on the seller. Look for warranty details on individual product pages or contact the seller directly.'
            ],
            [
                'title' => 'What animals can I buy or sell?',
                'description' => 'Anaaj Culture lists a range of livestock, including cows, goats, poultry, and more, based on availability.'
            ],
            [
                'title' => 'Are health checks included for animals?',
                'description' => 'Health check requirements vary by seller. Look for health certifications in the product description or verify with the seller.'
            ],
            [
                'title' => 'How do I hire labor for agricultural work?',
                'description' => 'Browse the labor category to find skilled laborers for tasks like planting, harvesting, or equipment maintenance.'
            ],
            [
                'title' => 'Can I list myself as a laborer on the app?',
                'description' => 'Yes, you can register as a labor provider and list your services under the labor category.'
            ]
        ];
        

        foreach ($faq as $info) {
            Faq::updateOrCreate(
                ['title' => $info['title']],
                [
                    'title' => $info['title'],
                    'description' => $info['description'],
                ]
            );
        }
    }
}
