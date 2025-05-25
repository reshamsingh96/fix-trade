<?php

namespace App\Http\Controllers;

use App\Constants\StatusConst;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBookmark;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Store;
use App\Models\SubCategory;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use stdClass;

class ProductController extends Controller
{
    const CONTROLLER_NAME = "App\Http\Controllers\ProductController";

    protected $user;
    protected $isSuperAdmin;
    protected $userUuid;

    private function checkAdminUser()
    {
        $this->user = auth()->user() ?? null;
        $this->isSuperAdmin = $this->user && $this->user->account_type == User::SUPER_ADMIN;
        $this->userUuid = $this->user ? $this->user->uuid : null;
    }

    public function productList(Request $request)
    {
        try {
            $params = ['category_id' => $request->category_id ?? null, 'sub_category_id' => $request->sub_category_id ?? null, 'search' => $request->search ?? null, 'row' => $request->perPage];
            $list = $this->_productList(...$params);
            return $this->actionSuccess('Products retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    private function _productList(string $category_id = null, string $sub_category_id = null, string $search = null, int $row = 25)
    {
        $this->checkAdminUser();

        $query = Product::query();

        $query->when(!$this->isSuperAdmin, function ($que) {
            $que->where('user_id', $this->userUuid);
        });

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if ($sub_category_id) {
            $query->where('sub_category_id', $sub_category_id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('product_search', 'like', "%$search%");
            });
        }
        return $query->latest()
            ->with('user:uuid,name', 'store', 'category:id,name,category_url', 'sub_category:id,name,sub_category_url', 'product_images:id,product_id,image_url')
            ->paginate($row);
    }

    public function dropdownUserProductList(Request $request)
    {
        $this->checkAdminUser();
        try {
            $category_id = $request->category_id ?? null;
            $sub_category_id = $request->sub_category_id ?? null;

            $query = Product::query()->where('status',StatusConst::ACTIVE);
            $query->when(!$this->isSuperAdmin, function ($que) {
                $que->where('user_id', $this->userUuid);
            });

            if ($category_id) {
                $query->where('category_id', $category_id);
            }

            if ($sub_category_id) {
                $query->where('sub_category_id', $sub_category_id);
            }

            $list = $query->latest()->select('id', 'name')->get();
            return $this->actionSuccess('Products retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function dropdownAllProductList(Request $request)
    {
        $this->checkAdminUser();
        try {
            $category_id = $request->category_id ?? null;
            $sub_category_id = $request->sub_category_id ?? null;

            $query = Product::query()->where('status',StatusConst::ACTIVE);

            $query->when(!$this->isSuperAdmin, function ($que) {
                $que->where('user_id', '!=', $this->userUuid);
            });

            if ($category_id) {
                $query->where('category_id', $category_id);
            }

            if ($sub_category_id) {
                $query->where('sub_category_id', $sub_category_id);
            }

            $list = $query->latest()->select('id', 'name')->get();
            return $this->actionSuccess('Products retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    // 'store_id' => 'required',
    public function productCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'description' => 'required',
            'type' => 'required',
            'quantity' => 'required',
            'status' => 'required',
            'unit_price' => 'required',
            'duration' => 'nullable',
            'unit_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'product_images' => 'required|array'
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        
        DB::beginTransaction();
        try {
            $user_id = auth()->user()->uuid;
            if (!isset($request->store_id) || !$request->store_id || $request->store_id == 'null') {
                $store_id = Store::where('user_id', $user_id)->pluck('id')->first();
            } else {
                $store_id = $request->store_id;
            }

            if(empty($store_id)) return $this->actionFailure('Store id Required!');

            $category_name = Category::where('id', $request->category_id)->pluck('name')->first();
            $sub_category_name = SubCategory::where('id', $request->sub_category_id)->pluck('name')->first();

            $name = $request->name . ' ' . $category_name . ' ' . $sub_category_name;
            $name = trim($name, '-');
            $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $name);
            $slug = strtolower($slug);
            $originalSlug = $slug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $latitude = $request->latitude ? $request->latitude : null;
            $longitude = $request->longitude ? $request->longitude : null;

            $params = ['amount' => (int)($request->unit_price ?? 0), 'discount' => ($request->discount ?? 0), 'discount_type' => $request->discount_type ?? StatusConst::FIXED];
            $discount = getDiscountPrice(...$params);

            $create = [
                'user_id' => $user_id,
                'store_id' => $store_id,
                'name' => $request->name,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'description' => $request->description,
                'type' => $request->type ? $request->type : StatusConst::ORDER_TYPE_SELLER,
                'slug' => $slug,
                'discount_type' => $request->discount_type ? $request->discount_type : StatusConst::FIXED,
                'discount' => $request->discount ? $request->discount : 0,
                'tax_type' => $request->tax_type ? $request->tax_type : StatusConst::EXCLUSIVE,
                'tax_id' => $request->tax_id ?? Tax::where('tax_name','GST 0%')->pluck('id')->first() ?? null,
                'status' => $request->status ? $request->status : StatusConst::ACTIVE,
                'comment' => $request->comment ? $request->comment : null,
                'product_search' => preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($name . $request->description)),
                'quantity' => (int)$request->quantity,
                'unit_price' => (int)$request->unit_price,
                'discount_unit_price' => (int)$discount,
                'duration' => $request->duration,
                'unit_id' => $request->unit_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];

            $product = Product::create($create);

            if ($product) {
                if ($request->product_images) {
                    foreach ($request->product_images as $key => $image) {
                        if (isset($image) && $image->isValid()) {
                            $uploadedFile = $image;
                            $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), ['folder' => 'product_images']);
                            ProductImage::create([
                                'product_id' => $product->id,
                                'image_url' => $uploadResult->getSecurePath(),
                                'image_public_id' => $uploadResult->getPublicId(),
                                'user_id' => $user_id,
                            ]);
                        }
                    }
                }
            }
            DB::commit();
            return $this->actionSuccess('Product created successfully.', $product);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function singleProductInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        try {
            $product = Product::where('id', $request->product_id)->with('user', 'user.user_address', 'category:id,name,category_url', 'sub_category:id,name,sub_category_url', 'store', 'product_images')->first();
            return $this->actionSuccess('Product info retrieved successfully.', $product);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function editProductInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        try {
            $product = Product::where('id', $request->id)->with('user:uuid,name', 'store', 'product_images')->first();
            return $this->actionSuccess('Product info retrieved successfully.', $product);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function productUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
            'name' => 'required',
            'store_id' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'description' => 'required',
            'type' => 'required',
            'status' => 'required',
            'quantity' => 'required',
            'unit_price' => 'required',
            'duration' => 'nullable',
            'unit_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            // 'product_images' => 'required|array',
            'delete_images' => 'array'
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $user_id = auth()->user()->uuid;
            $product = Product::find($request->product_id);

            if (!$product) {
                return $this->actionFailure('Product not found.');
            }

            if ($request->name !== $product->name || $request->description !== $product->description) {
                $category_name = Category::where('id', $request->category_id)->pluck('name')->first();
                $sub_category_name = SubCategory::where('id', $request->sub_category_id)->pluck('name')->first();

                $name = $request->name . ' ' . $category_name . ' ' . $sub_category_name;
                $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $name);
                $slug = trim($slug, '-');
                $slug = strtolower($slug);

                $existingSlugParts = explode('-', $product->slug);
                $lastPart = end($existingSlugParts);

                if (is_numeric($lastPart)) {
                    $slug = $slug . '-' . $lastPart;
                }

                $originalSlug = $slug;
                $counter = 1;
                while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $product->product_search = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($name . $request->description));
                $product->slug = $slug;
                $product->save();
            }

            if (!isset($request->store_id) || !$request->store_id) {
                $store_id = Store::where('user_id', $product->user_id)->pluck('id')->first();
            } else {
                $store_id = $request->store_id;
            }

            $latitude = $request->latitude ? $request->latitude : null;
            $longitude = $request->longitude ? $request->longitude : null;

            $params = ['amount' => (int)($request->unit_price ?? 0), 'discount' => ($request->discount ?? 0), 'discount_type' => $request->discount_type ?? StatusConst::FIXED];
            $discount = getDiscountPrice(...$params);

            // Update product details
            $product->update([
                'name' => $request->name,
                'store_id' => $store_id,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'description' => $request->description,
                'type' => $request->type ? $request->type : StatusConst::ORDER_TYPE_SELLER,
                'discount_type' => $request->discount_type ? $request->discount_type : StatusConst::FIXED,
                'discount' => $request->discount ? $request->discount : 0,
                'tax_type' => $request->tax_type ? $request->tax_type : StatusConst::EXCLUSIVE,
                'tax_id' => $request->tax_id ?? Tax::where('tax_name','GST 0%')->pluck('id')->first() ?? null,
                'status' => $request->status ? $request->status : StatusConst::ACTIVE,
                'comment' => $request->comment ? $request->comment : null,
                'quantity' => (int)$request->quantity,
                'unit_price' => (int)$request->unit_price,
                'discount_unit_price' => (int)$discount,
                'duration' => $request->duration,
                'unit_id' => $request->unit_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
            
            if ($request->delete_images && count($request->delete_images) > 0) {
                $old_images = ProductImage::whereIn('id', $request->delete_images)->get();
                foreach ($old_images as $image) {
                    Cloudinary::destroy($image->image_public_id);
                }
                ProductImage::whereIn('id', $request->delete_images)->delete();
            }

            // Upload new images
            if ($request->product_images) {
                foreach ($request->product_images as $image) {
                    if (isset($image['image_file']) && $image['image_file']->isValid()) {
                        $uploadedFile = $image['image_file'];
                        $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), ['folder' => 'product_images']);
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $uploadResult->getSecurePath(),
                            'image_public_id' => $uploadResult->getPublicId(),
                            'user_id' => $user_id,
                        ]);
                    }
                }
            }

            DB::commit();
            return $this->actionSuccess('Product updated successfully.', $product);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to update product: ' . $e->getMessage());
        }

        return $this->actionFailure('Product update request failed');
    }

    public function ProductUpdateStatus(Request $request)
    {
        $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'status' => 'required|string|in:Active,In-Active'
        ]);

        try {
            DB::beginTransaction();
            $product = Product::findOrFail($request->product_id);
            $product->status = $request->status;
            $product->save();
            DB::commit();
            return $this->actionSuccess('Product status updated successfully',$product);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure( $e->getMessage());
        }
    }


    public function productDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        // $exists = Order::where('product_id', $request->id)->exists();
        // if ($exists) {
        // return $this->actionFailure("If the Product already uses any Order, please do not delete it!");
        // }

        try {
            DB::beginTransaction();
            $product = Product::where('id', $request->id)->first();
            if ($product) {
                $delete_image = ProductImage::where('product_id', $request->id)->get();
                foreach ($delete_image as $image) {
                    Cloudinary::destroy($image->image_public_id);
                }
                $product->delete();
                DB::commit();
                return $this->actionSuccess('Product deleted successfully.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to delete product: ' . $e->getMessage());
        }
        return $this->actionFailure('Product deleted request failed');
    }

    // Product Review Functions
    public function productReviewList(Request $request)
    {
        try {
            $row = $request->perPage ?? 25;
            $search = $request->search ?? null;

            $query = Review::query();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('review', 'like', "%$search%");
                    if (!empty($request->user_id)) {
                        $q->orWhereHas('product', function ($que) use ($search) {
                            $que->where('name', 'like', "%$search%");
                        });
                    }
                    if (!empty($request->product_id)) {
                        $q->orWhereHas('user', function ($que) use ($search) {
                            $que->where('name', 'like', "%$search%");
                        });
                    }
                });
            }

            if (!empty($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }

            if (!empty($request->product_id)) {
                $query->where('product_id', $request->product_id);
            }

            $query->with('product:id,name', 'product.image:id,image_url', 'user:uuid,name,image_url');
            $reviews = $query->latest()->paginate($row);

            return $this->actionSuccess('Product reviews retrieved successfully', customizingResponseData($reviews));
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function productReviewCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review' => 'required',
            'rating' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        $user_id = $request->user_id ? $request->user_id : (Auth::check() ? auth()->user()->uuid : null);

        try {
            DB::beginTransaction();
            if ($user_id) {
                $create = [
                    'review' => $request->review,
                    'rating' => $request->rating,
                    'user_id' => $user_id,
                    'product_id' => $request->product_id,
                ];
                $info = Review::create($create);
                DB::commit();
                return $this->actionSuccess('Product review created successfully', $info);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->createExceptionError('Reviewed User Not Found');
    }

    public function productReviewUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'review' => 'required',
            'user_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $update = [
                'review' => $request->review,
            ];

            $info = Review::where('id', $request->id)->update($update);
            DB::commit();
            return $this->actionSuccess('Product review created successfully', $info);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->createExceptionError('Reviewed Update Request failed!');
    }

    public function productReviewDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $review = Review::where('id', $request->id)->first();
            if ($review) {
                $review->delete();
                DB::commit();
                return $this->actionSuccess('Product Review deleted successfully');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->createExceptionError('Product Review delete info Not Found!');
    }

    // Product Bookmark Functions
    public function productBookmarkList(Request $request)
    {
        try {
            if( isset($request->type) && $request->type == "web_list"){
            $row = $request->perPage ?? 25;
            $search = $request->search ?? null;

            $query = ProductBookmark::query();
            if (!empty($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }

            if (!empty($request->product_id)) {
                $query->where('product_id', $request->product_id);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    if (!empty($request->user_id)) {
                        $q->whereHas('product', function ($que) use ($search) {
                            $que->where('name', 'like', "%$search%");
                        });
                    }
                    if (!empty($request->product_id)) {
                        $q->whereHas('user', function ($que) use ($search) {
                            $que->where('name', 'like', "%$search%");
                        });
                    }
                });
            }

            $query->with('product:id,name', 'product.image:id,product_id,image_url', 'user:uuid,name,image_url');
            $list = $query->latest()->paginate($row);
        }else{
            $params = [
                'category_id' => @$request->category_id ?? null,
                'sub_category_id' => @$request->sub_category_id ?? null,
                'search' => @$request->search ?? null,
                'type' => @$request->type ?? 'All',
                'row' => @$request->perPage ?? 25,
                'latitude' => @$request->latitude ?? null,
                'longitude' => @$request->longitude ?? null,
                'user_id' => isset($request->user_id) && $request->user_id ? $request->user_id : null
            ];
            $list = $this->_mobileProductListBookmark(...$params);
        }
            return $this->actionSuccess('Product bookmarks retrieved successfully', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    private function _mobileProductListBookmark(string $category_id = null, string $sub_category_id = null, string $search = null, string $type = 'All', int $row = 25, $user_id = null, float $latitude = null, $longitude = null)
    {
        $this->checkAdminUser();

        $user_id = $user_id ? $user_id : $this->userUuid;

        $query = Product::query()->where('status',StatusConst::ACTIVE);

            $query->whereHas('bookmark_info', function ($que) {
                $que->where('user_id', $this->userUuid);
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('product_search', 'like', "%$search%");
            });
        }

        // If latitude and longitude are provided, calculate the distance
        if ($latitude && $longitude) {
            $query->selectRaw(
                "*, (6371 * acos(cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )
                ->having('distance', '<=', 50) // Optional radius filter (50 km)
                ->orderBy('distance'); // Order by proximity
        }

        $list = $query->latest()
            ->with('user:uuid,name', 'store', 'category:id,name,category_url', 'sub_category:id,name,sub_category_url', 'product_images:id,product_id,image_url')
            ->with([
                'bookmark_info' => function ($que) use ($user_id) {
                    $que->where('user_id',  $user_id);
                }
            ])
            ->paginate($row);

        return customizingResponseData($list);
    }

    public function productBookmarkCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_bookmark' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        $user_id = $request->user_id ? $request->user_id : (Auth::check() ? auth()->user()->uuid : null);

        try {
            DB::beginTransaction();
            if ($user_id) {
                $create = [
                    'is_bookmark' => $request->is_bookmark,
                    'user_id' => $user_id,
                    'product_id' => $request->product_id,
                ];
                $bookmark = ProductBookmark::where('user_id', $user_id)
                ->orWhere('product_id', $request->product_id)->first();
                if($bookmark){
                    $bookmark->delete();
                    $info = ['status' => 'deleted'];
                    $msg ="Successfully Product Removed from Bookmark" ;
                }else{
                    $info = ProductBookmark::create($create);
                    $msg ="Successfully Product Bookmark" ;
                }
                DB::commit();
                if( isset($request->type) && $request->type == "web_list"){
                }else{
                    $params = [
                        'category_id' => @$request->category_id ?? null,
                        'sub_category_id' => @$request->sub_category_id ?? null,
                        'search' => @$request->search ?? null,
                        'type' => @$request->type ?? 'All',
                        'row' => @$request->perPage ?? 25,
                        'latitude' => @$request->latitude ?? null,
                        'longitude' => @$request->longitude ?? null,
                        'user_id' => isset($request->user_id) && $request->user_id ? $request->user_id : null
                    ];
                    $info = $this->_mobileProductListBookmark(...$params);
                }
                
                return $this->actionSuccess($msg, $info);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->createExceptionError('Bookmark User Not Found');
    }

    public function productBookmarkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'is_bookmark' => 'required',
            'user_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            DB::beginTransaction();

            $update = [
                'is_bookmark' => $request->is_bookmark,
            ];

            $info = ProductBookmark::where('id', $request->id)->update($update);
            DB::commit();
            if( isset($request->type) && $request->type == "web_list"){
            }else{
                $params = [
                    'category_id' => @$request->category_id ?? null,
                    'sub_category_id' => @$request->sub_category_id ?? null,
                    'search' => @$request->search ?? null,
                    'type' => @$request->type ?? 'All',
                    'row' => @$request->perPage ?? 25,
                    'latitude' => @$request->latitude ?? null,
                    'longitude' => @$request->longitude ?? null,
                    'user_id' => isset($request->user_id) && $request->user_id ? $request->user_id : null
                ];
                $info = $this->_mobileProductListBookmark(...$params);
            }
            return $this->actionSuccess('Product Bookmark Update successfully', $info);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function productBookmarkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $bookmark = ProductBookmark::where('id', $request->id)->first();
            if ($bookmark) {
                $bookmark->delete();
                DB::commit();
                return $this->actionSuccess('Product bookmark deleted successfully');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Product bookmark deleted Request Failed');
    }

    public function webProductList(Request $request)
    {
        try {
            $params = [
                'category_id' => $request->category_id ?? null,
                'sub_category_id' => $request->sub_category_id ?? null,
                'search' => $request->search ?? null,
                'type' => $request->type ?? 'All',
                'row' => $request->perPage ?? 25,
                'user_id' => isset($request->user_id) && $request->user_id ? $request->user_id : null
            ];
            $list = $this->_webProductList(...$params);
            return $this->actionSuccess('Products retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Product deleted Request Failed');
    }

    private function _webProductList(string $category_id = null, string $sub_category_id = null, string $search = null, string $type = 'All', int $row = 25, $user_id = null)
    {
        $this->checkAdminUser();

        $user_id = $user_id ? $user_id : $this->userUuid;

        $query = Product::query()->where('status',StatusConst::ACTIVE);

        $query->when(!$this->isSuperAdmin, function ($que) {
            $que->where('user_id', '!=', $this->userUuid);
        });

        if ($type != 'All') {
            $query->where('type', $type);
        }

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if ($sub_category_id) {
            $query->where('sub_category_id', $sub_category_id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('product_search', 'like', "%$search%");
            });
        }

        $list = $query->latest()
            ->with('user:uuid,name', 'store', 'category:id,name,category_url', 'sub_category:id,name,sub_category_url', 'product_images:id,product_id,image_url')
            ->with([
                'bookmark_info' => function ($que) use ($user_id) {
                    $que->where('user_id',  $user_id);
                }
            ])
            ->paginate($row);
        return customizingResponseData($list);
    }

    public function mobileProductList(Request $request)
    {
        try {
            $params = [
                'category_id' => $request->category_id ?? null,
                'sub_category_id' => $request->sub_category_id ?? null,
                'search' => $request->search ?? null,
                'type' => $request->type ?? 'All',
                'row' => $request->perPage ?? 25,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'user_id' => isset($request->user_id) && $request->user_id ? $request->user_id : null
            ];
            $list = $this->_mobileProductList(...$params);
            return $this->actionSuccess('Products retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Product deleted Request Failed');
    }

    private function _mobileProductList(string $category_id = null, string $sub_category_id = null, string $search = null, string $type = 'All', int $row = 25, $user_id = null, float $latitude = null, $longitude = null)
    {
        $this->checkAdminUser();

        $user_id = $user_id ? $user_id : $this->userUuid;

        $query = Product::query()->where('status',StatusConst::ACTIVE);

        $query->when(!$this->isSuperAdmin, function ($que) {
            $que->where('user_id', '!=', $this->userUuid);
        });

        if ($type != 'All') {
            $query->where('type', $type);
        }

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if ($sub_category_id) {
            $query->where('sub_category_id', $sub_category_id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('product_search', 'like', "%$search%");
            });
        }

        // If latitude and longitude are provided, calculate the distance
        if ($latitude && $longitude) {
            $query->selectRaw(
                "*, (6371 * acos(cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )
                ->having('distance', '<=', 50) // Optional radius filter (50 km)
                ->orderBy('distance'); // Order by proximity
        }

        $list = $query->latest()
            ->with('user:uuid,name', 'store', 'category:id,name,category_url', 'sub_category:id,name,sub_category_url', 'product_images:id,product_id,image_url')
            ->with([
                'bookmark_info' => function ($que) use ($user_id) {
                    $que->where('user_id',  $user_id);
                }
            ])
            ->paginate($row);

        return customizingResponseData($list);
    }

    public function productDetail(Request $request)
    {
        try {
            $value = isset($request->id) && $request->id ? $request->id : $request->slug;
            $product = Product::where('slug', $value)->where('status',StatusConst::ACTIVE)
                ->orWhere('id', $value)
                ->withCount('rating')
                ->with([
                    'user',
                    'user.user_address',
                    'category:id,name,category_url',
                    'sub_category:id,name,sub_category_url',
                    'store',
                    'product_images:id,product_id,image_url',
                    'tax',
                    'user_rating' => function ($query) use ($request, $value) {
                        $userId = $request->guest_user_id ? $request->guest_user_id : (auth()->id() ?? null);

                        $query->whereHas('product', function ($q) use ($value) {
                            $q->where('slug', $value)->orWhere('id', $value);
                        })
                            ->where(function ($q) use ($userId) {
                                $q->where('user_id', $userId)
                                    ->orWhere('guest_user_id', $userId);
                            });
                    }
                ])
                ->first();


            // Calculate the total possible rating (5 points for each rating)
            $total = $product->rating_count * 5;
            $rating_sum = $product->rating_sum;

            // Calculate the percentage based on rating_sum and total possible rating
            if ($total > 0) {
                $percentage = ($rating_sum / $total) * 100;
                $rating = convertPercentageToRating($percentage);
            } else {
                $percentage = 0;
                $rating = 0;
            }

            $product->percentage_rating = $rating;

            return $this->actionSuccess('Product detail successfully.', $product);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Product detail request Failed');
    }

    // Review Functions
    public function reviewList(Request $request)
    {
        try {
            $value = isset($request->product_id) && $request->product_id ? $request->product_id : $request->slug;
            $product_id = Product::where('slug', $value)->orWhere('id', $value)->pluck('id')->first();
            $row = $request->perPage ?? 10;

            $query = Review::query();
            $query->where('product_id', $product_id);
            $query->with('product:id,name', 'user:uuid,name,image_url');
            $reviews = $query->latest()->paginate($row);

            return $this->actionSuccess('Product reviews retrieved successfully', customizingResponseData($reviews));
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function webProductRating(Request $request)
    {
        try {
            $info = new stdClass();
            $value = isset($request->id) && $request->id ? $request->id : $request->slug;
            $product = Product::where('slug', $value)->orWhere('id', $value)->withCount('rating')->withSum('rating as rating_sum', 'rating')->first();

            $ratings = $product->rating()->select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->get();

            // Calculate total ratings
            $totalRatings = $ratings->sum('count');

            // Calculate percentages for each rating level (1 to 5)
            $ratingPercentages = [];
            for ($i = 1; $i <= 5; $i++) {
                $count = $ratings->where('rating', $i)->first()->count ?? 0;
                $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                $ratingPercentages[$i] = convertPercentageToRating($percentage);
            }

            $total = $product->rating_count * 5;
            $rating_sum = $product->rating_sum;

            if ($total > 0) {
                $percentage = ($rating_sum / $total) * 100;
                $rating = convertPercentageToRating($percentage);
            } else {
                $percentage = 0;
                $rating = 0;
            };

            $info->rating_list = $ratingPercentages;
            $info->percentage = $percentage;
            $info->rating = $rating;
            $info->rating_count = $product->rating_count;

            return $this->actionSuccess('Product Rating Percentages successfully.', $info);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Product Rating Percentages request Failed');
    }
}
