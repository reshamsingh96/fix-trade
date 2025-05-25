<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Frontend
Route::get('/', [RouteController::class, 'homePage']);

Route::get('/login', [RouteController::class, 'loginPage']);
Route::get('/register', [RouteController::class, 'registerPage']);
Route::get('/forgot-password', [RouteController::class, 'forgetPasswordPage']);
Route::get('/home', [RouteController::class, 'homePage']);

Route::get('/products', [RouteController::class, 'products']);
Route::get('/product/{slug}', [RouteController::class, 'productDetail']);

Route::get('/labors', [RouteController::class, 'labors']);
Route::get('/labor/{id}', [RouteController::class, 'laborDetail']);

Route::get('/cart', [RouteController::class, 'cartPage']);

Route::get('/contact-us', [RouteController::class, 'contactUs']);

Route::get('/terms-conditions', [RouteController::class, 'termsConditions']);

Route::get('/privacy-policy', [RouteController::class, 'privacyPolicy']);

Route::get('/refund-policy', [RouteController::class, 'refundPolicy']);

Route::get('/checkout', [RouteController::class, 'checkoutPage']);

Route::get('/my-account', [RouteController::class, 'accountPage']);

// Admin
Route::get('/admin/{any}', function () { return view('appVue'); })->where('any', '.*');

Route::redirect('/admin', '/admin/login');
