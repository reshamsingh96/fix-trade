<?php

namespace Database\Seeders;

use App\Constants\StatusConst;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBookmark;
use App\Models\Review;
use App\Models\Store;
use App\Models\SubCategory;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Database\Seeder;

class productSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run()
  {
    $id = User::pluck('uuid')->first();
    $category_list = Category::get();
    $tax = Tax::where('tax_name', 'GST 18%')->first();

    $create = [
      'user_id' => $id,
      'store_url' => '/images/shiva_group.jpg',
      'type' => 'Shop',
      'store_name' => 'Shiva Agrotech',
      'full_address' => '',
      'gst_number' => '',
      'status' => StatusConst::ACTIVE,
    ];
    $store = Store::updateOrCreate(['user_id' => $id], $create);

    $store_id = $store->id;

    foreach ($category_list as $key => $category) {

      // "https://images.unsplash.com/photo-1693667308707-1ce4fabfa78d?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8cmVkJTIwY293fGVufDB8fDB8fHww"

      $sub_category = SubCategory::where('category_id', $category->id)->select('id', 'name')->first();
      $category_name = $category->name;
      $sub_category_name = $sub_category->name;

      $name = $category_name . ' ' . $sub_category_name;
      $name = trim($name, '-');
      $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $name);
      $slug = strtolower($slug);
      $originalSlug = $slug;
      $counter = 1;
      while (Product::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
      }
      $unit_price = 1000 * ($key + 1);
      $product = Product::updateOrCreate([
        'user_id' => $id,
        'store_id' => $store_id,
        'name' => $category->name . ' ' . $sub_category->name,
        'slug' => $slug,
        'description' => 'this category product is best',
        'category_id' => $category->id,
        'sub_category_id' => $sub_category->id,
        'discount_type' => StatusConst::FIXED,
        'discount' => 100,
        'type' => StatusConst::ORDER_TYPE_SELLER,
        'tax_type' => StatusConst::EXCLUSIVE,
        'tax_id' => $tax->id,
        'status' => StatusConst::ACTIVE,
        'latitude' => 30.711406,
        'longitude' => 76.691839,
        'quantity' => $key + 5,
        'unit_price' => $unit_price,
        'discount_unit_price' => $unit_price - 100,
        'duration' => 0,
        'unit_id'=>null,
      ]);

      $product->product_search = $product->name;
      $product->save();

      $user_id = User::where('account_type', 'User')->pluck('uuid')->first();
      Review::updateOrCreate(['review' => "This is best product " . $category->name . ' ' . $sub_category->name, 'user_id' => $user_id, 'rating' => rand(1, 5), 'product_id' => $product->id]);
      ProductBookmark::updateOrCreate(['is_bookmark' => true, 'user_id' => $user_id, 'product_id' => $product->id]);
    }
  }
}
