<?php

namespace App\Http\Controllers;

use App\Constants\CommonConst;
use App\Constants\RoleConst;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use App\Mail\PasswordResetMail;
use App\Models\City;
use App\Models\Country;
use App\Models\Labour;
use App\Models\Role;
use App\Models\State;
use App\Models\Store;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Aws\Sns\SnsClient;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class LoginController extends Controller
{
    const CONTROLLER_NAME = "App\Http\Controllers\LoginController";

    public function LoginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        try {
            # Attempt to find the user by email, username, or phone, excluding inactive users
            $user = User::where(function ($query) use ($request) {
                $query->where('email', $request->user_name)->orWhere('user_name', $request->user_name)->orWhere('phone', $request->user_name);
            })->where('status', '!=', CommonConst::IN_ACTIVE)->first();

            # Validate password and user existence
            if (!$user) {
                return $this->actionFailure('User not found or account is inactive.');
            }

            if (!Hash::check($request->password, $user->password)) {
                return $this->actionFailure('Incorrect password. Please try again.');
            }

            # Log in the user (not required for Sanctum token issuing, but optional)
            $token = $user->createToken('API Token')->plainTextToken;

            $credentials = ['email' => $user->email, 'user_name' => $user->user_name, 'password' => $request->password];

            if ($request->remember_me) {
                auth('web')->attempt($credentials, true);
            } else {
                auth('web')->attempt($credentials);
            }

            $info = adminAddLoginUserLog($user, $request);

            $response = [
                'access_token' => $token,
                'permissions' => $user->getPermissionsViaRoles(),
                'user' => $user->makeHidden('roles'),
                'status' => true
            ];

            return $this->actionSuccess("Login Successfully", $response);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
 
        return $this->actionFailure('email or Password incorrect!');
    }

    public function logout(Request $request)
    {
        $tokenId = Str::before($request->bearerToken(), '|');
        DB::table('personal_access_tokens')->where('id', $tokenId)->delete();
        return $this->actionSuccess('Logged out successfully');
    }

    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|same:confirm_password',
            'phone_code' => 'required|string',
            'phone' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg,webp|max:2048',
            'secondary_number' => 'nullable|string',
            'account_type' => 'required|in:Admin,User,Vendor,Labor',
            'state' => 'required',
            'city' => 'required',
            'pin_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        // if (User::where('email', $request->email)->exists()) {
        //     return $this->actionFailure("Your Email Already Exists!");
        // }

        if (User::where('phone', $request->phone)->exists()) {
            return $this->actionFailure("Your phone Number Already Exists!");
        }

        DB::beginTransaction();
        try {
            $image_url = null;
            $image_public_id = null;
            if ($request->hasFile('profile_image')) {
                $uploadedFile = $request->file('profile_image');
                $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'users',
                ]);
                $image_url = $uploadResult->getSecurePath();
                $image_public_id = $uploadResult->getPublicId();
            }

            $create = [
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'account_type' => $request->account_type,
                'phone_code' => $request->phone_code,
                'phone' => $request->phone,
                'secondary_number' => $request->secondary_number,
                'search_key' => $request->name . $request->email . $request->phone . $request->secondary_number,
                'is_buyer' => $request->is_buyer == 'false' || $request->is_buyer == false ? false : true,
                'is_seller' => $request->is_seller == 'false' || $request->is_seller == false ? false : true,
                'is_labor' => $request->is_labor == 'false' || $request->is_labor == false ? false : true,
                'image_url' => $image_url,
                'image_public_id' => $image_public_id,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                "user_name"=>$request->user_name ?? $request->phone_code,
                'password' => Hash::make($request->password),
            ];

            $user = User::create($create);
            storeCreateDefault($user->uuid);
            if ($request->account_type == User::LABOR) {
                $create = [
                    'user_id' => $user->uuid,
                    'labor_name' => $request->name,
                    'work_title' => '  ',
                    'phone' => $request->phone,
                    'status' => $request->status,
                    'description' => @$request->description,
                    'image_url' => $image_url,
                    'image_public_id' => $image_public_id,
                ];

                $labor = Labour::create($create);
            }

            // Assign roles to users
            $slug = $request->account_type == User::USER ? RoleConst::USER : ($request->account_type == User::LABOR ? RoleConst::LABOR : RoleConst::VENDOR);
            $role_ids = Role::where('slug', $slug)->pluck('uuid')->toArray();
            updateUserRoles($role_ids, $user);
            $country_id = Country::where('name', 'India')->pluck('id')->first() ?? null;
            $state_id = State::where('country_id', $country_id)->where('name', 'LIKE', '%' . $request->state . '%')->pluck('id')->first() ?? null;
            $city_id = City::where('country_id', $country_id)->where('state_id', $state_id)->where('name', 'LIKE', '%' . $request->city . '%')->pluck('id')->first() ?? null;

            UserAddress::create([
                'user_id' => $user->uuid,
                'country_id' => $country_id,
                'state_id' => $state_id,
                'city_id' => $city_id,
                'pin_code' => $request->pin_code,
                'home_no' => $request->home_no,
                'full_address' => $request->full_address,
            ]);

            # Log in the user (not required for Sanctum token issuing, but optional)
            $token = $user->createToken('API Token')->plainTextToken;
            $credentials = ['email' => $user->email, 'phone' => $user->phone, 'user_name' => $user->user_name, 'password' => $request->password];
            if ($request->remember_me) {
                auth('web')->attempt($credentials, true);
            } else {
                auth('web')->attempt($credentials);
            }

            $response['access_token'] = $token;
            $response['permissions'] = $user->getPermissionsViaRoles();
            $response['user'] = $user;
            $response['store'] = Store::where('user_id',$user->uuid)->get();
            DB::commit();

            return $this->actionSuccess("User created successfully", $response);
            // return $this->actionSuccess('User created successfully', $user);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'type' => 'required|in:direct,email,phone',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->first();

            if ($request->type == 'direct') {
                $password = "qwerty123";
                $user->password = Hash::make($password);
                $user->save();
                DB::commit();
                return $this->actionSuccess("Your new password is: $password", $user);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }

        return $this->actionFailure('Failed to send password reset link!');
    }

    public function postCodeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        try {
            // Make the HTTP request to fetch the address by pin code
            $response = Http::get("https://api.postalpincode.in/pincode/" . $request->post_code);

            // Check if the response was successful
            if ($response->successful()) {
                $addressData = $response->json();
                if (!empty($addressData) && isset($addressData[0]['PostOffice'])) {
                    $addressInfo = $addressData[0]['PostOffice'][0];

                    return $this->actionSuccess('Address details retrieved successfully.', $addressInfo);
                } else {
                    return $this->actionFailure('Invalid pin_code or no address found.');
                }
            } else {
                return $this->actionFailure('Failed to retrieve address from the external API.');
            }
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }
}
