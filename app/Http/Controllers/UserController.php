<?php

namespace App\Http\Controllers;

use App\Constants\RoleConst;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\GuestUser;
use App\Models\Labour;
use App\Models\State;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

const USER_CONTROLLER = "Http / User Controller";
class UserController extends Controller
{
    function __construct()
    {
    }

    public function guestUserCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:guest_users,email',
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'pin_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            DB::beginTransaction();

            $ipAddress = $request->ip();

            // Create a new guest user record
            $guestUser = GuestUser::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'country' => $request->input('country'),
                'ip_address' => $ipAddress,
            ]);

            DB::commit();
            return $this->actionSuccess('Guest user created successfully', $guestUser);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->actionFailure($e->getMessage());
        }
    }

    /**
     * Display a listing of users.
     *
     * This function retrieves a paginated list of users based on the provided search criteria and pagination settings.
     * It uses the `_userList` method to query the users and returns a success response with the user data.
     *
     * @group User Management
     * @bodyParam search string optional Search keyword for filtering users.
     * @bodyParam perPage integer optional Number of users per page. Defaults to 25.
     * @return \Illuminate\Http\Response
     */
    public function userList(Request $request)
    {
        try {
            $params = ['search' => $request->search ?? null, 'row' => $request->perPage, 'status' => $request->status];
            $list = $this->_userList(...$params);
            return $this->actionSuccess('User List retrieved successfully', $list);
        } catch (\Exception $e) {
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    /**
     * Query the user list.
     *
     * This internal function performs the database query to retrieve users based on search criteria.
     * The results are paginated based on the specified number of rows per page.
     *
     * @param string|null $search Search keyword for filtering users.
     * @param int $row Number of users per page.
     */
    public function _userList(?string $search = null, int $row = 25, $status)
    {
        $query = User::query()->where('account_type', '!=', User::SUPER_ADMIN);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('search_key', 'like', "%$search%");
            });
        }

        if ($status && $status != 'All') $query->where('status', $status);
        return $query->latest()->with('user_address')->paginate($row);
    }

    public function dropdownUserList(Request $request)
    {
        try {
            $query = User::query()->where('account_type', '!=', User::SUPER_ADMIN);
            $list = $query->latest()->select('uuid', 'name')->get();
            return $this->actionSuccess('User retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function dropdownTaxList(Request $request)
    {
        try {
            $list = Tax::select('id', 'tax_name', 'tax_percentage')->get();
            return $this->actionSuccess('Tax retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function dropdownUnitList(Request $request)
    {
        try {
            $list = Unit::select('id', 'name')->get();
            return $this->actionSuccess('Unit retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }


    /**
     * Create a new user.
     *
     * This function handles the creation of a new user. It validates the input data, hashes the password,
     * assigns roles to the user, and commits the changes to the database. If any error occurs, the transaction is rolled back.
     *
     * @group User Management
     * @bodyParam name string required Name of the user.
     * @bodyParam email string required Email of the user. Must be unique.
     * @bodyParam password string required Password for the user.
     * @bodyParam confirm-password string required Password confirmation. Must match the password.
     * @bodyParam roles array required Array of role IDs to assign to the user.
     * @bodyParam phone_code string optional Phone code of the user.
     * @bodyParam phone string optional Primary phone number of the user.
     * @bodyParam secondary_number string optional Secondary phone number of the user.
     * @bodyParam account_type string required Account type of the user. Must be either "Admin" or "User".
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function userCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required',
            'phone_code' => 'required|string',
            'phone' => 'required|string',
            'secondary_number' => 'nullable|string',
            'account_type' => 'required|in:Super Admin,User',
            // 'country_id' => 'required',
            'state' => 'required',
            'city' => 'required',
            'pin_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (User::where('email', $request->email)->exists()) {
            return $this->actionFailure("Your Email Already Exists!");
        }

        if (User::where('phone', $request->phone)->exists()) {
            return $this->actionFailure("Your phone Number Already Exists!");
        }

        DB::beginTransaction();
        try {

            $image_url = null;
            $image_public_id = null;
            if ($request->hasFile('image')) {
                $uploadedFile = $request->file('image');
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
                'zip_code' => $request->zip_code,
                'secondary_number' => $request->secondary_number,
                'search_key' => $request->name . $request->email . $request->phone . $request->secondary_number,
                'is_buyer' => $request->is_buyer == 'false' || $request->is_buyer == false ? false : true,
                'is_seller' => $request->is_seller == 'false' || $request->is_seller == false ? false : true,
                'is_labor' => $request->is_labor == 'false' || $request->is_labor == false ? false : true,
                'image_url' => $image_url,
                'image_public_id' => $image_public_id,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'password' => Hash::make($request->password),
            ];

            $user = User::create($create);
            // storeCreateDefault($user->uuid);

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

            # Assign roles to users
            $role_ids = Role::where('uuid', $request->role_id)->pluck('uuid')->toArray();
            $role_ids = Role::where('uuid', $request->role_id)->pluck('uuid')->toArray();
            if (count($role_ids) == 0) {
                $slug = $request->account_type == User::USER ? RoleConst::USER : ($request->account_type == User::LABOR ? RoleConst::LABOR : RoleConst::VENDOR);
                $role_ids = Role::where('slug', $slug)->pluck('uuid')->toArray();
                updateUserRoles($role_ids, $user);
            }else{
                updateUserRoles($role_ids, $user);
            }
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

            DB::commit();
            return $this->actionSuccess('User created successfully', $user);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    /**
     * Retrieve single user information.
     *
     * This function retrieves detailed information about a specific user based on their user ID.
     * It returns a success response with the user's information or an error if something goes wrong.
     *
     * @group User Management
     * @bodyParam user_id required The ID of the user.
     * @return \Illuminate\Http\Response
     */
    public function singleUserInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            $user = User::where('uuid', $request->user_id)->with('user_address')->first();
            return $this->actionSuccess('User information retrieved successfully', $user);
        } catch (\Exception $e) {
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    /**
     * Update a user's information.
     *
     * This function updates the specified user's details, including their roles and permissions.
     * It validates the input, updates the user record, detaches previous roles, and assigns new roles.
     * The transaction is rolled back if any error occurs.
     *
     * @group User Management
     * @bodyParam user_id integer required The ID of the user.
     * @bodyParam name string required Name of the user.
     * @bodyParam email string required Email of the user. Must be unique.
     * @bodyParam password string optional Password for the user.
     * @bodyParam confirm-password string optional Password confirmation. Must match the password.
     * @bodyParam roles array required Array of role IDs to assign to the user.
     * @bodyParam phone_code string optional Phone code of the user.
     * @bodyParam phone string optional Primary phone number of the user.
     * @bodyParam secondary_number string optional Secondary phone number of the user.
     * @bodyParam account_type string required Account type of the user. Must be either "Admin" or "User".
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function userUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_code' => 'required',
            'phone' => 'required',
           
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        // if (User::where('uuid', '!=', $request->user_id)->where('email', $request->email)->exists()) {
        //     return $this->actionFailure("Your Email Already Exists!");
        // }

        if (User::where('uuid', '!=', $request->user_id)->where('phone', $request->phone)->exists()) {
            return $this->actionFailure("Your phone Number Already Exists!");
        }

        DB::beginTransaction();
        try {
            $update = [];

            // Update fields only if they are present in the request
            if (isset($request->name)) {
                $update['name'] = $request->name;
            }
            if (isset($request->email)) {
                $update['email'] = $request->email;
            }
            if (isset($request->gender)) {
                $update['gender'] = $request->gender;
            }
            if (isset($request->account_type)) {
                $update['account_type'] = $request->account_type;
            }
            if (isset($request->phone_code)) {
                $update['phone_code'] = $request->phone_code;
            }
            if (isset($request->zip_code)) {
                $update['zip_code'] = $request->zip_code;
            }
            if (isset($request->phone)) {
                $update['phone'] = $request->phone;
            }
            if (isset($request->secondary_number)) {
                $update['secondary_number'] = $request->secondary_number;
            }
            if (isset($request->name) || isset($request->email) || isset($request->phone) || isset($request->secondary_number)) {
                $update['search_key'] = $request->name . $request->email . $request->phone . $request->secondary_number;
            }
            if (isset($request->is_buyer)) {
                $update['is_buyer'] = $request->is_buyer == 'false' || $request->is_buyer == false ? false : true;
            }
            if (isset($request->is_seller)) {
                $update['is_seller'] = $request->is_seller == 'false' || $request->is_seller == false ? false : true;
            }
            if (isset($request->is_labor)) {
                $update['is_labor'] = $request->is_labor == 'false' || $request->is_labor == false ? false : true;
            }
            
            $user = User::where('uuid', $request->user_id)->first();
            $user->update($update);
            
            if ($request->hasFile('image')) {
                if ($user->image_public_id) {
                    Cloudinary::destroy($user->image_public_id);
                }

                // Upload the new image
                $uploadedFile = $request->file('image');
                $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'users',
                ]);
                $user->image_url = $uploadResult->getSecurePath();
                $user->image_public_id = $uploadResult->getPublicId();
                $user->save();
            }

            // $user->roles()->detach();
            $role_ids = Role::where('uuid', $request->role_id)->pluck('uuid')->toArray();
            if (count($role_ids) == 0) {
                $slug = $request->account_type == User::USER ? RoleConst::USER : ($request->account_type == User::LABOR ? RoleConst::LABOR : RoleConst::VENDOR);
                $role_ids = Role::where('slug', $slug)->pluck('uuid')->toArray();
                updateUserRoles($role_ids, $user);
            }else{
                updateUserRoles($role_ids, $user);
            }
            $country_id = Country::where('name', 'India')->pluck('id')->first() ?? null;
            $state_id = State::where('country_id', $country_id)->where('name', 'LIKE', '%' . $request->state . '%')->pluck('id')->first() ?? null;
            $city_id = City::where('country_id', $country_id)->where('state_id', $state_id)->where('name', 'LIKE', '%' . $request->city . '%')->pluck('id')->first() ?? null;

            UserAddress::updateOrCreate(['id' => $request->address_id, 'user_id' => $user->uuid], [
                'user_id' => $user->uuid,
                'country_id' => $country_id,
                'state_id' => $state_id,
                'city_id' => $city_id,
                'pin_code' => $request->pin_code,
                'home_no' => $request->home_no,
                'full_address' => $request->full_address,
            ]);

            DB::commit();
            return $this->actionSuccess('User updated successfully', $user);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('User updated Request Failed');
    }

    public function userUpdateStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,uuid',
            'status' => 'required|string|in:Active,In-Active'
        ]);

        try {
            DB::beginTransaction();
            $user = User::where('uuid',$request->user_id)->first();
            $user->status = $request->status;
            $user->save();
            DB::commit();
            return $this->actionSuccess('User status updated successfully',$user);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure( $e->getMessage());
        }
    }

    /**
     * Delete a user.
     *
     * This function deletes the specified user from the database based on their ID.
     * The deletion is wrapped in a transaction, which is rolled back if any error occurs.
     *
     * @group User Management
     * @urlParam id integer required The ID of the user to delete.
     * @return json Response
     */
    public function userDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delete_type' => 'required|in:Delete,Restore,Force Delete',
            'user_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $user = User::withTrashed()->where('uuid', $request->user_id)->firstOrFail();

            switch ($request->delete_type) {
                case 'Delete':
                    // Soft delete the user
                    $user->status = 'In-Active';
                    $user->delete();
                    $message = 'User Soft Deleted Successfully';
                    break;
                case 'Restore':
                    // Restore the soft deleted user
                    $user->status = 'Active';
                    $user->restore();
                    $message = 'User Restored Successfully';
                    break;
                case 'Force Delete':
                    // Permanently delete the user
                    if ($user->image_public_id) {
                        Cloudinary::destroy($user->image_public_id);
                    }
                    $user->forceDelete();
                    $message = 'User Permanently Deleted Successfully';
                    break;
                default:
                    $message = 'Delete Type not match';
            }

            DB::commit();
            return $this->actionSuccess($message, $user);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }

        return $this->actionFailure('User Delete request failed');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'user_id' => 'required',
            'password' => 'required',
            'password' => 'required|min:6|confirmed', // 'confirmed' will now match with 'password_confirmation'
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            $user = User::where('uuid', $request->user_id)->first();

            if (!$user) {
                return $this->actionFailure('User not found');
            }

            // if (!Hash::check($request->old_password, $user->password)) {
            //     return $this->actionFailure('Old password does not match');
            // }

            $user->password = Hash::make($request->password);
            $user->save();

            DB::commit();
            return $this->actionSuccess('Password changed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, USER_CONTROLLER, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Change password request failed!');
    }
}
