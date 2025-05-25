<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Labour;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    const CONTROLLER_NAME = "App\Http\Controllers\DashboardController";
    
    protected $user;
    protected $isSuperAdmin;
    protected $userUuid;
   
    private function checkAdminUser(){
        $this->user = auth()->user() ?? null;
        $this->isSuperAdmin = $this->user && $this->user->account_type == User::SUPER_ADMIN;
        $this->userUuid = $this->user ? $this->user->uuid : null;
    }

    public function getDashboardCounts()
    {
        try {
            $this->checkAdminUser();
            $list = [];
            if ($this->isSuperAdmin) {
                $list = [
                    'users_count' => User::where('account_type','!=',User::SUPER_ADMIN)->count(),
                    'products_count' => Product::count(),
                    'orders_count' => 0, //?? Order::count(),
                    'labors_count' => Labour::count(),
                ];
            } else {
                $list = [
                    'users_count' => User::where('account_type','!=',User::SUPER_ADMIN)->where('uuid', $this->userUuid)->count(),
                    'products_count' => Product::where('user_id', $this->userUuid)->count(),
                    'orders_count' => 0, //?? Order::where('user_id', $this->userUuid)->count(),
                    'labors_count' => Labour::where('user_id', $this->userUuid)->count(),
                ];
            }

            return $this->actionSuccess('Dashboard Count retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function getLatestFiveProducts()
    {
        $this->checkAdminUser();
        try {
            $products = Product::when(!$this->isSuperAdmin, function ($query) {
                $query->where('user_id', $this->userUuid);
            })->orderBy('created_at', 'desc')
            ->with('image:id,product_id,image_url')
            ->take(5)->get();

            return $this->actionSuccess('Latest Five Products retrieved successfully.', $products);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function getLatestFiveOrders()
    {
        $this->checkAdminUser();
        try {
            $orders = [];
            // $orders = Order::when(!$this->isSuperAdmin, function ($query) {
            //     $query->where('user_id', $this->userUuid);
            // })->orderBy('created_at', 'desc')->take(5)->get();

            return $this->actionSuccess('Latest Five Orders retrieved successfully.', $orders);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function getProductCategoryCount()
    {
        $this->checkAdminUser();
        try {
            $categories = Category::withCount(['product' => function ($query) {
                $query->when(!$this->isSuperAdmin, function ($query) {
                    $query->where('user_id', $this->userUuid);
                });
            }])->get();

            return $this->actionSuccess('Product Category Count retrieved successfully.', $categories);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function getSubCategoryProductCount()
    {
        $this->checkAdminUser();
        try {
            $subCategories = Category::with(['sub_category' => function ($query) {
                $query->withCount(['product' => function ($query) {
                    $query->when(!$this->isSuperAdmin, function ($query) {
                        $query->where('user_id', $this->userUuid);
                    });
                }]);
            }])->get();

            // $subCategories = SubCategory::withCount(['product' => function ($query) {
            //     $query->when(!$this->isSuperAdmin, function ($query) {
            //         $query->where('user_id', $this->userUuid);
            //     });
            // }])->with('category:id,name,category_url')->get();

            return $this->actionSuccess('Sub Category Product Count retrieved successfully.', $subCategories);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function getCategorySubCategoryCount()
    {
        try {
            $categories = Category::withCount('sub_category')->get();
            return $this->actionSuccess('Category Sub-Category Count retrieved successfully.', $categories);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }
}
