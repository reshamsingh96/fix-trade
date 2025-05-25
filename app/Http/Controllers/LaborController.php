<?php

namespace App\Http\Controllers;

use App\Constants\StatusConst;
use Carbon\Carbon;
use App\Models\Labour;
use App\Models\LaborDayWorking;
use App\Models\LaborImage;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;
use stdClass;

class LaborController extends Controller
{
    const CONTROLLER_NAME = "App\Http\Controllers\LaborController";

    protected $user;
    protected $isSuperAdmin;
    protected $userUuid;

    private function checkAdminUser()
    {
        $this->user = auth()->user() ?? null;
        $this->isSuperAdmin = $this->user && $this->user->account_type == User::SUPER_ADMIN;
        $this->userUuid = $this->user ? $this->user->uuid : null;
    }

    public function webLaborList(Request $request)
    {
        try {
            $params = [
                'search' => $request->search ?? null,
                'row' => $request->perPage ?? 25,
                'user_id' => isset($request->user_id) && $request->user_id ? $request->user_id : null
            ];
            $list = $this->_webLaborList(...$params);
            return $this->actionSuccess('Products retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Product deleted Request Failed');
    }

    private function _webLaborList(string $search = null, int $row = 25, $user_id = null)
    {
        $this->checkAdminUser();

        $user_id = $user_id ? $user_id : $this->userUuid;

        $query = Labour::query()->where('status', StatusConst::ACTIVE);

        $query->when(!$this->isSuperAdmin, function ($que) {
            $que->where('user_id', '!=', $this->userUuid);
        });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('work_title', 'like', "%$search%")
                    ->orWhere('labor_name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        $list = $query->latest()
            ->withMin('working_day as min_amount', 'day_amount')
            ->withMax('working_day as max_amount', 'day_amount')
            ->with('user:uuid,name')
            ->paginate($row);
        return customizingResponseData($list);
    }

    public function laborDetail(Request $request)
    {
        try {
            $value = isset($request->id) && $request->id ? $request->id : $request->slug;
            $labor = Labour::where('id', $value)
                ->withMin('working_day as min_amount', 'day_amount')
                ->withMax('working_day as max_amount', 'day_amount')
                ->with([
                    'user',
                    'working_day',
                    'user.user_address',
                    'work_images',
                    'user_rating' => function ($query) use ($request, $value) {
                        $userId = $request->guest_user_id ? $request->guest_user_id : (auth()->user()->uuid ?? null);

                        $query->whereHas('labour', function ($q) use ($value) {
                            $q->where('id', $value);
                        })
                            ->where(function ($q) use ($userId) {
                                $q->where('user_id', $userId)
                                    ->orWhere('guest_user_id', $userId);
                            });
                    }
                ])
                ->first();

            return $this->actionSuccess('Labor detail successfully.', $labor);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Labor detail request Failed');
    }

    public function laborList(Request $request)
    {
        try {
            $params = ['search' => $request->search ?? null, 'row' => $request->perPage, 'user_id' => $request->user_id];
            $list = $this->_laborList(...$params);
            return $this->actionSuccess('Laborers retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to retrieve laborers: ' . $e->getMessage());
        }
    }

    public function _laborList(string $search = null, int $row = 25, $user_id = null)
    {
        $user = auth()->user() ?? null;
        $query = Labour::query();

        if ($user_id) {
            $query->where('user_id', $user_id);
        } else if ($user && $user->account_type != User::SUPER_ADMIN) {
            $query->where('user_id', $user->uuid);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('work_title', 'like', "%$search%")
                    ->orWhere('labor_name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }
        return $query->latest()->with('user:uuid,name', 'work_images', 'working_day')->paginate($row);
    }

    public function laborCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'labor_name' => 'required',
            'work_title' => 'required',
            'phone' => 'required',
            'status' => 'required',
            'labor_day_working' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (Labour::where('work_title', $request->work_title)->where('phone', $request->phone)->exists()) {
            return $this->actionFailure("Labor User Already Exists!");
        }

        try {
            DB::beginTransaction();
            $user_id = auth()->user()->uuid;
            $image_url = null;
            $image_public_id = null;
            if ($request->hasFile('image')) {
                $uploadedFile = $request->file('image');
                $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'labor',
                ]);
                $image_url = $uploadResult->getSecurePath();
                $image_public_id = $uploadResult->getPublicId();
            }

            $create = [
                'user_id' => $user_id,
                'labor_name' => $request->labor_name,
                'work_title' => $request->work_title,
                'phone' => $request->phone,
                'status' => $request->status,
                'description' => $request->description,
                'image_url' => $image_url,
                'image_public_id' => $image_public_id,
            ];

            $labor = Labour::create($create);

            if ($request->labour_images) {
                foreach ($request->labour_images as $key => $image) {
                    if (isset($image['image_file']) && $image['image_file']->isValid()) {
                        $uploadedFile = $image['image_file'];
                        $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                            'folder' => 'labor',
                        ]);
                        LaborImage::create([
                            'image_url' => $uploadResult->getSecurePath(),
                            'image_public_id' => $uploadResult->getPublicId(),
                            'labour_id' => $labor->id,
                        ]);
                    }
                }
            }

            //    $request->labor_day_working = [
            //     ['day_name' => 'Sunday', 'day_number' => 0, 'start_time' => '08:00', 'end_time' => '17:20', 'break_minute' => 60, 'per_hour_amount' => 100],
            // ];

            foreach ($request->labor_day_working as $info) {
                $per_mint_amount = $info['per_hour_amount'] / 60;

                // Convert start_time and end_time to Carbon instances
                $start = Carbon::createFromFormat('H:i', $info['start_time']);
                $end = Carbon::createFromFormat('H:i', $info['end_time']);
                $total_minutes = $end->diffInMinutes($start);
                $working_minutes = ($total_minutes - $info['break_minute']);
                $working_hour = (int) ($working_minutes / 60) . ':' . (int) $working_minutes - ((int)($working_minutes / 60) * 60);
                $day_amount = $working_minutes * $per_mint_amount;
                $create = [
                    'labour_id' => $labor->id,
                    'day_number' => $info['day_number'],
                    'day_name' => $info['day_name'],
                    'start_time' => $info['start_time'],
                    'end_time' => $info['end_time'],
                    'break_minute' => $info['break_minute'],
                    'working_hour' => $working_hour,
                    'per_hour_amount' => $info['per_hour_amount'],
                    'day_amount' => round($day_amount),
                ];

                $working_info = LaborDayWorking::updateOrCreate(['labour_id' => $labor->id, 'day_number' => $info['day_number']], $create);
            }
            DB::commit();
            return $this->actionSuccess('Labor created successfully.', $labor);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function singleLaborInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            $labor = Labour::where('id', $request->id)->with('user:uuid,name', 'work_images', 'working_day')->first();
            return $this->actionSuccess('Labor info retrieved successfully.', $labor);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to retrieve labor info: ' . $e->getMessage());
        }
    }

    public function laborUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'user_id' => 'required',
            'labor_name' => 'required',
            'work_title' => 'required',
            'phone' => 'required',
            'status' => 'required',
            'labor_day_working' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (Labour::where('id', '!=', $request->id)->where('work_title', $request->work_title)->where('phone', $request->phone)->exists()) {
            return $this->actionFailure("Labor User Already Exists!");
        }

        try {
            DB::beginTransaction();
            $update = [
                'user_id' => $request->user_id,
                'labor_name' => $request->labor_name,
                'work_title' => $request->work_title,
                'phone' => $request->phone,
                'status' => $request->status,
                'description' => $request->description
            ];

            $labor = Labour::where('id', $request->id)->first();

            if ($labor) {
                $labor->update($update);

                if ($request->hasFile('image')) {
                    if ($labor->image_public_id) {
                        Cloudinary::destroy($labor->image_public_id);
                    }

                    // Upload the new image
                    $uploadedFile = $request->file('image');
                    $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                        'folder' => 'labor',
                    ]);
                    $labor->image_url = $uploadResult->getSecurePath();
                    $labor->image_public_id = $uploadResult->getPublicId();
                    $labor->save();
                }

                $labor_image_ids = $request->labour_images ? array_column($request->labour_images, 'labor_image_id') : [];

                $unused_images = LaborImage::where('labour_id', $labor->id)
                    ->whereNotIn('id', $labor_image_ids)
                    ->get();

                foreach ($unused_images as $image) {
                    Cloudinary::destroy($image->image_public_id);
                    $image->delete();
                }

                if ($request->labour_images) {
                    foreach ($request->labour_images as $key => $image) {
                        if (isset($image['image_file']) && $image['image_file']->isValid()) {

                            $uploadedFile = $image['image_file'];
                            $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                                'folder' => 'labor',
                            ]);
                            LaborImage::create([
                                'image_url' => $uploadResult->getSecurePath(),
                                'image_public_id' => $uploadResult->getPublicId(),
                                'labour_id' => $labor->id,
                            ]);
                        }
                    }
                }

                $day_number = array_column($request->labor_day_working, 'day_number');
                LaborDayWorking::where('labour_id', $labor->id)->whereNotIn('day_number', $day_number)->delete();

                foreach ($request->labor_day_working as $info) {
                    $per_mint_amount = $info['per_hour_amount'] / 60;
                    $start = Carbon::createFromFormat('H:i', $info['start_time']);
                    $end = Carbon::createFromFormat('H:i', $info['end_time']);
                    $total_minutes = $end->diffInMinutes($start);
                    $working_minutes = ($total_minutes - $info['break_minute']);
                    $working_hour = (int) ($working_minutes / 60) . ':' . (int) $working_minutes - ((int)($working_minutes / 60) * 60);
                    $day_amount = $working_minutes * $per_mint_amount;

                    $update = [
                        'labour_id' => $labor->id,
                        'day_number' => $info['day_number'],
                        'day_name' => $info['day_name'],
                        'start_time' => $info['start_time'],
                        'end_time' => $info['end_time'],
                        'break_minute' => $info['break_minute'],
                        'working_hour' => $working_hour,
                        'per_hour_amount' => $info['per_hour_amount'],
                        'day_amount' => round($day_amount),
                    ];

                    $working = LaborDayWorking::updateOrCreate(['labour_id' => $labor->id, 'day_number' => $info['day_number']], $update);
                }
                DB::commit();
                return $this->actionSuccess('Laborer updated successfully.', $labor);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to update laborer: ' . $e->getMessage());
        }
        return $this->actionSuccess('Laborer update request failed!');
    }

    public function labourUpdateStatus(Request $request)
    {
        $request->validate([
            'labour_id' => 'required|uuid|exists:Labours,id',
            'status' => 'required|string|in:Active,In-Active'
        ]);

        try {
            DB::beginTransaction();
            $user = Labour::where('id',$request->labour_id)->first();
            $user->status = $request->status;
            $user->save();
            DB::commit();
            return $this->actionSuccess('labour status updated successfully',$user);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure( $e->getMessage());
        }
    }

    public function laborDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $labor = Labour::where('id', $request->id)->first();
            if ($labor) {
                $unused_images = LaborImage::where('labour_id', $labor->id)->get();

                foreach ($unused_images as $image) {
                    Cloudinary::destroy($image->image_public_id);
                }
                $labor->delete();
                DB::commit();
                return $this->actionSuccess('Laborer deleted successfully.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to delete laborer: ' . $e->getMessage());
        }
        return $this->actionSuccess('Laborer deleted Request Failed!');
    }

    // labour Review Functions
    public function labourReviewList(Request $request)
    {
        try {
            $row = $request->perPage ?? 10;
            $query = Review::query();
            $query->where('labour_id', $request->labour_id);
            $query->with('labour', 'user:uuid,name,image_url');
            $reviews = $query->latest()->paginate($row);

            return $this->actionSuccess('Labour reviews retrieved successfully', customizingResponseData($reviews));
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function webLabourRating(Request $request)
    {
        try {
            $info = new stdClass();
            $value = isset($request->labour_id) && $request->labour_id ? $request->labour_id : $request->slug;
            $Labour = Labour::where('id', $value)->withCount('rating')->withSum('rating as rating_sum', 'rating')->first();

            $ratings = $Labour->rating()->select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->get();

            $totalRatings = $ratings->sum('count');

            $ratingPercentages = [];
            for ($i = 1; $i <= 5; $i++) {
                $count = $ratings->where('rating', $i)->first()->count ?? 0;
                $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                $ratingPercentages[$i] = convertPercentageToRating($percentage);
            }

            $total = $Labour->rating_count * 5;
            $rating_sum = $Labour->rating_sum;

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
            $info->rating_count = $Labour->rating_count;

            return $this->actionSuccess('Labour Rating Percentages successfully.', $info);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Labour Rating Percentages request Failed');
    }

    public function labourReviewCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review' => 'required',
            'rating' => 'required',
            'labour_id' => 'required',
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
                    'labour_id' => $request->labour_id,
                ];
                $info = Review::create($create);
                DB::commit();
                return $this->actionSuccess('Labour review created successfully', $info);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->createExceptionError('Labour Reviewed User Not Found');
    }
}
