<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function loginPage(Request $request)
    {
        return view('frontend.auth.login', ['title' => 'login']);
    }

    public function registerPage(Request $request)
    {
        return view('frontend.auth.register', ['title' => 'Register']);
    }

    public function forgetPasswordPage(Request $request)
    {
        return view('frontend.auth.forgetPassword', ['title' => 'Forget Password']);
    }

    public function homePage(Request $request)
    {
        return view('frontend.home', ['title' => 'Home']);
    }

    public function products(Request $request)
    {
        return view('frontend.products.products', ['title' => 'Products']);
    }

    public function productDetail(Request $request)
    {
        return view('frontend.products.productDetail', ['title' => 'Product Detail']);
    }

    public function labors(Request $request)
    {
        return view('frontend.labors.labor', ['title' => 'Labors']);
    }

    public function laborDetail(Request $request)
    {
        return view('frontend.labors.laborDetail', ['title' => 'Labor Detail']);
    }

    public function cartPage(Request $request)
    {
        return view('frontend.cart.cartPage', ['title' => 'Cart']);
    }

    public function checkoutPage(Request $request)
    {
        return view('frontend.checkout.checkout', ['title' => 'Checkout']);
    }

    public function accountPage(Request $request)
    {
        return view('frontend.account.account', ['title' => 'My Account']);
    }

    public function contactUs(Request $request)
    {
        return view('frontend.support.contact_us', ['title' => 'Contact Us']);
    }

    public function termsConditions(Request $request)
    {
        return view('frontend.support.terms_condition', ['title' => 'Terms & Conditions']);
    }

    public function privacyPolicy(Request $request)
    {
        return view('frontend.support.privacy_policy', ['title' => 'Privacy Policy']);
    } 

    public function refundPolicy(Request $request)
    {
        return view('frontend.support.refund_policy', ['title' => 'Refund Policy']);
    } 
 
}
