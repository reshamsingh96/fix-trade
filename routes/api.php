<?php

use App\Http\Controllers\BellNotificationController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\LaborController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FaqsController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TableHeaderManageController;
use App\Http\Controllers\WhatsAppController;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login-user', [LoginController::class, 'LoginUser']);
Route::post('/register-user', [LoginController::class, 'registerUser'])->name('register.user');
Route::post('/forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot-password.user');
Route::post('/check-post-code', [LoginController::class, 'postCodeAddress']);

Route::post('/get-state-city-id', [CountryController::class, 'getStateCityId'])->name('dropdown.country.list');

Route::post('/get-state-city-id', [CountryController::class, 'getStateCityId'])->name('dropdown.country.list');

Route::post('/dropdown-country-list', [CountryController::class, 'dropdownCountryList'])->name('dropdown.country.list');
Route::post('/dropdown-state-list', [CountryController::class, 'dropdownStateList'])->name('dropdown.state.list');
Route::post('/dropdown-city-list', [CountryController::class, 'dropdownCityList'])->name('dropdown.city.list');
Route::post('/dropdown-category-list', [CategoryController::class, 'dropdownCategoryList'])->name('dropdown.category.list');
Route::post('/dropdown-sub-category-list', [CategoryController::class, 'dropdownSubCategoryList'])->name('dropdown.sub.category.list');
Route::post('/dropdown-all-product-list', [ProductController::class, 'dropdownAllProductList'])->name('dropdown.product.list');
Route::post('/dropdown-user-list', [UserController::class, 'dropdownUserList'])->name('dropdown.user.list');
Route::post('/dropdown-tax-list', [UserController::class, 'dropdownTaxList'])->name('dropdown.tax.list');
Route::post('/dropdown-unit-list', [UserController::class, 'dropdownUnitList'])->name('dropdown.unit.list');

Route::post('/web-product-list', [ProductController::class, 'webProductList'])->name('web.product.list');
Route::post('/product-review-list', [ProductController::class, 'reviewList'])->name('product.review.list');
Route::post('/product-detail', [ProductController::class, 'productDetail'])->name('product.detail');
Route::post('/web-product-rating', [ProductController::class, 'webProductRating'])->name('web-product-rating');
Route::post('/guest-user-create', [UserController::class, 'guestUserCreate']);

Route::post('/web-labor-list', [LaborController::class, 'webLaborList'])->name('web.labor.list');
Route::post('/labour-review-list', [LaborController::class, 'labourReviewList'])->name('labour.review.list');
Route::post('/web-labour-rating', [LaborController::class, 'webLabourRating'])->name('web-labour-rating');
Route::post('/labor-detail', [LaborController::class, 'laborDetail'])->name('labor.detail');
Route::post('/get-faqs-list', [FaqsController::class, 'getFaqs'])->name('faqs.list');


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/dropdown-user-product-list', [ProductController::class, 'dropdownUserProductList'])->name('dropdown.product.list');
    Route::get('/user', function (Request $request) { 
        $user = $request->user()->load('store');
        return $user;
    })->name('user');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/change-password', [UserController::class, 'changePassword']);

    # Dashboard Info api
    Route::get('/card-dashboard-count', [DashboardController::class, 'getDashboardCounts']);
    Route::get('/latest-five-product', [DashboardController::class, 'getLatestFiveProducts']);
    Route::get('/latest-five-order', [DashboardController::class, 'getLatestFiveOrders']);
    Route::get('/category-product-count', [DashboardController::class, 'getProductCategoryCount']);
    Route::get('/sub-category-product-count', [DashboardController::class, 'getSubCategoryProductCount']);
    Route::get('/category-sub-category-count', [DashboardController::class, 'getCategorySubCategoryCount']);
    
    # User Info
    Route::post('/user-list', [UserController::class, 'userList'])->name('user.list');
    Route::post('/user-create', [UserController::class, 'userCreate'])->name('user.create');
    Route::post('/single-user-info', [UserController::class, 'singleUserInfo'])->name('user.info');
    Route::post('/user-update', [UserController::class, 'userUpdate'])->name('user.update');
    Route::post('/user-update-status', [UserController::class, 'userUpdateStatus'])->name('user.update.status');
    Route::post('/user-delete', [UserController::class, 'userDelete'])->name('user.delete');

    # Store Info
    Route::post('/dropdown-store-list', [StoreController::class, 'dropdownStoreList'])->name('dropdown.store.list');
    Route::post('/store-list', [StoreController::class, 'storeList'])->name('store.list');
    Route::post('/store-create', [StoreController::class, 'storeCreate'])->name('store.create');
    Route::post('/single-store-info', [StoreController::class, 'singleStoreInfo'])->name('store.info');
    Route::post('/store-update', [StoreController::class, 'storeUpdate'])->name('store.update');
    Route::post('/store-delete', [StoreController::class, 'storeDelete'])->name('store.delete');

    # Country Info
    Route::post('/country-list', [CountryController::class, 'countryList'])->name('country.list');
    Route::post('/country-create', [CountryController::class, 'countryCreate'])->name('country.create');
    Route::post('/country-update', [CountryController::class, 'countryUpdate'])->name('country.update');
    Route::post('/country-delete', [CountryController::class, 'countryDelete'])->name('country.delete');

    # State Info
    Route::post('/state-list', [CountryController::class, 'stateList'])->name('state.list');
    Route::post('/state-create', [CountryController::class, 'stateCreate'])->name('state.create');
    Route::post('/state-update', [CountryController::class, 'stateUpdate'])->name('state.update');
    Route::post('/state-delete', [CountryController::class, 'stateDelete'])->name('state.delete');

    # City Info
    Route::post('/city-list', [CountryController::class, 'cityList'])->name('city.list');
    Route::post('/city-create', [CountryController::class, 'cityCreate'])->name('city.create');
    Route::post('/city-update', [CountryController::class, 'cityUpdate'])->name('city.update');
    Route::post('/city-delete', [CountryController::class, 'cityDelete'])->name('city.delete');

    # Product Category Info
    Route::post('/category-list', [CategoryController::class, 'categoryList'])->name('category.list');
    Route::post('/category-create', [CategoryController::class, 'categoryCreate'])->name('category.create');
    Route::post('/category-update', [CategoryController::class, 'categoryUpdate'])->name('category.update');
    Route::post('/category-delete', [CategoryController::class, 'categoryDelete'])->name('category.delete');

    # Product Sub Category Info
    Route::post('/sub-category-list', [CategoryController::class, 'subCategoryList'])->name('sub.category.list');
    Route::post('/sub-category-create', [CategoryController::class, 'subCategoryCreate'])->name('sub.category.create');
    Route::post('/sub-category-update', [CategoryController::class, 'subCategoryUpdate'])->name('sub.category.update');
    Route::post('/sub-category-delete', [CategoryController::class, 'subCategoryDelete'])->name('sub.category.delete');

    # Product Info
    Route::post('/get-product-list', [ProductController::class, 'mobileProductList'])->name('web.product.list');
    Route::post('/product-list', [ProductController::class, 'productList'])->name('product.list');

    Route::post('/product-create', [ProductController::class, 'productCreate'])->name('product.create');
    Route::post('/edit-product-info', [ProductController::class, 'editProductInfo'])->name('edit.product.info');
    Route::post('/single-product-info', [ProductController::class, 'singleProductInfo'])->name('single.product.info');
    Route::post('/product-update', [ProductController::class, 'productUpdate'])->name('product.update');
    Route::post('/product-update-status', [ProductController::class, 'productUpdateStatus'])->name('product.update.status');
    Route::post('/product-delete', [ProductController::class, 'productDelete'])->name('product.delete');

    # Product Review 
    Route::post('/product-review-list', [ProductController::class, 'productReviewList'])->name('product.review.list');
    Route::post('/product-review-create', [ProductController::class, 'productReviewCreate'])->name('product.review.create');
    Route::post('/labour-review-create', [LaborController::class, 'labourReviewCreate'])->name('labour.review.create');
    Route::post('/product-review-update', [ProductController::class, 'productReviewUpdate'])->name('product.review.update');
    Route::post('/product-review-delete', [ProductController::class, 'productReviewDelete'])->name('product.review.delete');
    
    # Product Bookmark 
    Route::post('/product-bookmark-list', [ProductController::class, 'productBookmarkList'])->name('product.bookmark.list');
    Route::post('/product-bookmark-create', [ProductController::class, 'productBookmarkCreate'])->name('product.bookmark.create');
    Route::post('/product-bookmark-update', [ProductController::class, 'productBookmarkUpdate'])->name('product.bookmark.update');
    Route::post('/product-bookmark-delete', [ProductController::class, 'productBookmarkDelete'])->name('product.bookmark.delete');
 
    # Order Info
    Route::post('/order-list', [OrderController::class, 'orderList'])->name('order.list');
    Route::post('/order-create', [OrderController::class, 'orderCreate'])->name('order.create');
    Route::post('/single-order-info', [OrderController::class, 'singleOrderInfo'])->name('order.info');
    Route::post('/order-update', [OrderController::class, 'orderUpdate'])->name('order.update');
    Route::post('/order-status-update', [OrderController::class, 'orderStatusUpdate'])->name('order.status.update');
    Route::post('/order-delete', [OrderController::class, 'orderDelete'])->name('order.delete');
    
    # Labor Info
    Route::post('/labor-list', [LaborController::class, 'laborList'])->name('labor.list');
    Route::post('/labor-create', [LaborController::class, 'laborCreate'])->name('labor.create');
    Route::post('/single-labor-info', [LaborController::class, 'singleLaborInfo'])->name('labor.info');
    Route::post('/labor-update', [LaborController::class, 'laborUpdate'])->name('labor.update');
    Route::post('/labour-update-status', [LaborController::class, 'labourUpdateStatus'])->name('labour.update.status');
    Route::post('/labor-delete', [LaborController::class, 'laborDelete'])->name('labor.delete');
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Route::get('/all-notification-count', [RuleController::class, 'allNotificationCount']);
    // Route::get('/all-notification-latest-five-list', [RuleController::class, 'allNotificationLatestFiveList']);

    # Table Header Manage Api
    Route::group(['prefix' => 'table-header'], function () {
        Route::get('/get', [TableHeaderManageController::class, 'getTableHeaders']);
        Route::post('/save', [TableHeaderManageController::class, 'saveTableHeaders']);
        Route::post('/sync', [TableHeaderManageController::class, 'tableHeaderSync']);
    });

    # Email api function
    Route::group(['prefix' => 'email'], function () {
        # Notification api
        Route::get('/notification-count', [EmailController::class, 'emailNotificationCount']);
        Route::get('/latest-five-notification-list', [EmailController::class, 'emailLatestFiveNotificationList']);
        Route::post('/mark-all-read-or-un-read', [EmailController::class, 'emailMarkAllReadOrUnRead']);
        Route::post('/is-read-notification', [EmailController::class, 'emailIsReadNotification']);

        # Notification Log api
        Route::get('/log-list', [EmailController::class, 'emailLogList']);
        Route::post('/update-read-status', [EmailController::class, 'emailUpdateReadStatusUpdate']);
        Route::post('/status-update', [EmailController::class, 'emailStatusUpdate'])->name('email.status.update');
        Route::delete('/delete-notification/{notification_type_id}', [EmailController::class, 'emailDeleteNotification']);

        # Utility api
        Route::get('/category-list', [EmailController::class, 'emailCategoryList']);
        Route::put('/create-update-template/{notification_type_id}', [EmailController::class, 'emailCreateUpdateTemplate']);
        Route::post('/preview', [EmailController::class, 'emailPreview']);
        Route::post('/send-notification', [EmailController::class, 'emailSendNotification']);
    });

    # Whats App api function
    Route::group(['prefix' => 'whatsApp'], function () {
        # Notification api
        Route::get('/notification-count', [WhatsAppController::class, 'whatsAppNotificationCount']);
        Route::get('/latest-five-notification-list', [WhatsAppController::class, 'whatsAppLatestFiveNotificationList']);
        Route::post('/mark-all-read-or-un-read', [WhatsAppController::class, 'whatsAppMarkAllReadOrUnRead']);
        Route::post('/is-read-notification', [WhatsAppController::class, 'whatsAppIsReadNotification']);

        # Notification Log api
        Route::get('/log-list', [WhatsAppController::class, 'whatsAppLogList']);
        Route::post('/update-read-status', [WhatsAppController::class, 'whatsAppUpdateReadStatusUpdate']);
        Route::post('/status-update', [WhatsAppController::class, 'whatsAppStatusUpdate'])->name('whatsApp.status.update');
        Route::delete('/delete-notification/{notification_type_id}', [WhatsAppController::class, 'whatsAppDeleteNotification']);

        # Utility api
        Route::get('/category-list', [WhatsAppController::class, 'whatsAppCategoryList']);
        Route::put('/create-update-template/{notification_type_id}', [WhatsAppController::class, 'whatsAppCreateUpdateTemplate']);
        Route::post('/preview', [WhatsAppController::class, 'whatsAppPreview']);
        Route::post('/send-notification', [WhatsAppController::class, 'whatsAppSendNotification']);
    });

    # Sms api function
    Route::group(['prefix' => 'sms'], function () {
        # Notification api
        Route::get('/notification-count', [SmsController::class, 'smsNotificationCount']);
        Route::get('/latest-five-notification-list', [SmsController::class, 'smsLatestFiveNotificationList']);
        Route::post('/mark-all-read-or-un-read', [SmsController::class, 'smsMarkAllReadOrUnRead']);
        Route::post('/is-read-notification', [SmsController::class, 'smsIsReadNotification']);

        # Notification Log api
        Route::get('/log-list', [SmsController::class, 'smsLogList']);
        Route::post('/update-read-status', [SmsController::class, 'smsUpdateReadStatusUpdate']);
        Route::post('/status-update', [SmsController::class, 'smsStatusUpdate'])->name('sms.status.update');
        Route::delete('/delete-notification/{notification_type_id}', [SmsController::class, 'smsDeleteNotification']);

        # Utility api
        Route::get('/category-list', [SmsController::class, 'smsCategoryList']);
        Route::put('/create-update-template/{notification_type_id}', [SmsController::class, 'smsCreateUpdateTemplate']);
        Route::post('/preview', [SmsController::class, 'smsPreview']);
        Route::post('/send-notification', [SmsController::class, 'smsSendNotification']);
    });

    # Bell Notification api function
    Route::group(['prefix' => 'bell'], function () {
        # Notification api
        Route::get('/notification-count', [BellNotificationController::class, 'bellNotificationCount']);
        Route::get('/latest-five-notification-list', [BellNotificationController::class, 'bellLatestFiveNotificationList']);
        Route::post('/mark-all-read-or-un-read', [BellNotificationController::class, 'bellMarkAllReadOrUnRead']);
        Route::post('/is-read-notification', [BellNotificationController::class, 'bellIsReadNotification']);

        # Notification Log api
        Route::get('/log-list', [BellNotificationController::class, 'bellLogList']);
        Route::post('/update-read-status', [BellNotificationController::class, 'bellUpdateReadStatusUpdate']);
        Route::post('/status-update', [BellNotificationController::class, 'bellStatusUpdate'])->name('bell.status.update');
        Route::delete('/delete-notification/{notification_type_id}', [BellNotificationController::class, 'bellDeleteNotification']);

        # Utility api
        Route::get('/category-list', [BellNotificationController::class, 'bellCategoryList']);
        Route::put('/create-update-template/{notification_type_id}', [BellNotificationController::class, 'bellCreateUpdateTemplate']);
        Route::post('/preview', [BellNotificationController::class, 'bellPreview']);
        Route::post('/send-notification', [BellNotificationController::class, 'bellSendNotification']);
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::group(['prefix' => 'role'], function () {
        # Role api Name
        Route::get('/', [RolePermissionController::class, 'getRoleList'])->name('role.index');
        Route::get('/rank/target', [RolePermissionController::class, 'getTargetRankList'])->name('role.rank.target');
        Route::get('/duplicate/{role_id}', [RolePermissionController::class, 'duplicateRoleCreate'])->name('role.index');
        Route::post('/info', [RolePermissionController::class, 'getRoleInfo'])->name('role.info');
        Route::post('/create', [RolePermissionController::class, 'saveRole'])->name('role.create');
        Route::post('/update', [RolePermissionController::class, 'saveRole'])->name('role.update');
        Route::get('/legal', [RolePermissionController::class, 'getLegalRole'])->name('role.legal');
        Route::get('/roll-assign-permission-list', [RolePermissionController::class, 'rollAssignPermissionList'])->name('role.assign.permission');
        Route::delete('/{role_id}', [RolePermissionController::class, 'roleDelete'])->name('role.delete');
        Route::get('/user-permission', [RolePermissionController::class, 'userPermission'])->name('user.permission');
    });
    # Permission api Name
    Route::group(['prefix' => 'permission'], function () {
        Route::get('/', [RolePermissionController::class, 'getPermissionList'])->name('permission.index');
    });
});