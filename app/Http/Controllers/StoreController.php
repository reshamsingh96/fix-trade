<?php

namespace App\Http\Controllers;

use App\Constants\StatusConst;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class StoreController extends Controller
{
    const CONTROLLER_NAME = "App\Http\Controllers\StoreController";

    protected $user;
    protected $isSuperAdmin;
    protected $userUuid;

    private function checkAdminUser()
    {
        $this->user = auth()->user() ?? null;
        $this->isSuperAdmin = $this->user && $this->user->account_type == User::SUPER_ADMIN;
        $this->userUuid = $this->user ? $this->user->uuid : null;
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
    public function storeList(Request $request)
    {
        try {
            $this->checkAdminUser();
            $params = ['search' => $request->search ?? null, 'user_id' => $request->user_id ?? null, 'row' => $request->perPage, 'status' => $request->status];
            $list = $this->_storeList(...$params);
            return $this->actionSuccess('Store List retrieved successfully', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
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
    public function _storeList(string $search = null, string $user_id = null, int $row = 25, $status)
    {
        $query = Store::query();

        $query->whereHas('user', function ($que) {
            $que->when(!$this->isSuperAdmin, function ($que) {
                $que->where('uuid', $this->userUuid);
            });
        });

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%$search%")
                    ->orWhere('full_address', 'like', "%$search%")
                    ->orWhere('gst_number', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($status && $status != 'All') $query->where('status', $status);
        return $query->latest()->paginate($row);
    }

    public function dropdownStoreList(Request $request)
    {
        try {
            $query = Store::query();

            $query->whereHas('user', function ($que) {
                $que->when(!$this->isSuperAdmin, function ($que) {
                    $que->where('uuid', $this->userUuid);
                });
            });

            $list = $query->latest()->select('id', 'store_name', 'store_url')->get();
            return $this->actionSuccess('Store retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    // gst: {
    //     valid: (value) =>
    //       /^([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[0-9]{1}[Z]{1}[0-9]{1})$/.test(
    //         value
    //       ),
    //   },

    //placeholder="GST Number Ex. 22AAAAA0000A1Z5" 

    public function storeCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_phone' => 'nullable',
            'type' => 'nullable|string',
            'store_name' => 'nullable|string',
            'full_address' => 'nullable',
            'gst_number' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        DB::beginTransaction();
        try {

            $store_url = null;
            $store_public_id = null;
            if ($request->hasFile('image')) {
                $uploadedFile = $request->file('image');
                $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'store',
                ]);
                $store_url = $uploadResult->getSecurePath();
                $store_public_id = $uploadResult->getPublicId();
            }

            $create = [
                'user_id' => auth()->check() ? auth()->user()->uuid : $request->user_id,
                'store_url' => $store_url,
                'type' => $request->type,
                'store_name' => $request->store_name,
                'store_phone' => $request->store_phone,
                'full_address' => $request->full_address,
                'store_public_id' => $store_public_id,
                'gst_number' => $request->gst_number,
                'status' => StatusConst::ACTIVE,
                'description' => $request->description
            ];

            $store = Store::create($create);
            DB::commit();
            return $this->actionSuccess('Store created successfully', $store);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function singleStoreInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            $store = Store::where('id', $request->store_id)->with('user')->first();
            return $this->actionSuccess('Store information retrieved successfully', $store);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function storeUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'store_phone' => 'nullable',
            'type' => 'required|string',
            'store_name' => 'required|string',
            'full_address' => 'required',
            'gst_number' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            $update = [
                'type' => $request->type,
                'store_name' => $request->store_name,
                'store_phone' => $request->store_phone,
                'full_address' => $request->full_address,
                'gst_number' => $request->gst_number,
                'description' => $request->description
            ];

            $store = Store::where('id', $request->store_id)->first();
            $store->update($update);

            if ($request->hasFile('image')) {
                if ($store->store_public_id) {
                    Cloudinary::destroy($store->store_public_id);
                }

                $uploadedFile = $request->file('image');
                $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'store',
                ]);
                $store->store_url = $uploadResult->getSecurePath();
                $store->store_public_id = $uploadResult->getPublicId();
                $store->save();
            }

            DB::commit();
            return $this->actionSuccess('Store updated successfully', $store);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Store updated Request Failed');
    }

    public function storeDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        // Check for existing product association
        if (Product::where('store_id', $request->store_id)->exists()) {
            return $this->actionFailure('Store is associated with products and cannot be deleted!');
        }

        DB::beginTransaction();
        try {
            $store = Store::find($request->store_id);

            if (!$store) {
                return $this->actionFailure('Store not found.');
            }

            if ($store->store_public_id) {
                Cloudinary::destroy($store->store_public_id);
            }

            $store->delete();
            DB::commit();

            return $this->actionSuccess('Store permanently deleted successfully.', $store);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to delete the store: ' . $e->getMessage());
        }
    }
}
