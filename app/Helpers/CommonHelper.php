<?php

use App\Constants\CommonConst;
use App\Constants\RoleConst;
use App\Constants\StatusConst;
use App\Models\ExceptionLog;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\Store;
use App\Models\TableHeaderManage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Role;
use App\Models\UserRole;
use CloudinaryLabs\CloudinaryLaravel\CloudinaryEngine;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Http;
// use Nwidart\Modules\Facades\Module;

const COMMON_HELPER = 'Helper / Common Helper';

function i($msg)
{
    Log::info($msg);
}

function er($msg)
{
    Log::error($msg);
}

/**
 * Format a date according to a “type” key.
 *
 * @param  \DateTime|string|null  $date
 * @param  string                 $type
 * @return string
 */
function formatAccordingDateTime($date, string $type): string
{
    if (empty($date)) $date = Carbon::now();

    # ensure we have a Carbon instance
    $dt = $date instanceof Carbon ? $date : Carbon::parse($date);

    switch ($type) {
        case 'time_ago':       # diffForHumans()
            return $dt->diffForHumans();

        case 'm-d-y':          # MM-DD-YYYY
            return $dt->format('m-d-Y');

        case 'd-m-y':          # DD-MM-YYYY
            return $dt->format('d-m-Y');

        case 'd-m-y-time':     # 12h with AM/PM
            return $dt->format('d-m-Y h:i A');

        case 'y-m-d':          # YYYY-MM-DD
            return $dt->format('Y-m-d');

        case 'd M, y':         # DD Mon, YYYY
            return $dt->format('d M, Y');

        case 'full_date':      # Friday, April 18th 2025
            return $dt->format('l, F jS Y');

        case 'full_date_1':    # Friday, April 18, 2025
            return $dt->format('l, F j, Y');

        case 'date_time':      # YYYY-MM-DD hh:mm AM/PM
            return $dt->format('Y-m-d h:i A');

        case 'time_only':      # 03:45 PM
            return $dt->format('h:i A');

        case 'month_year':     # April 2025
            return $dt->format('F Y');

        case 'iso':            # ISO 8601
            return $dt->toIso8601String();

        case 'custom_1':       # Apr 18, 2025
            return $dt->format('M j, Y');

        case 'custom_2':       # 18 Apr 2025, 3:45 PM
            return $dt->format('j M Y, g:i A');

        case 'd-m-y-his':      # 15-05-2025 15:26:42
            return $dt->format('d-m-Y H:i:s');

        default:
            # fallback: full Y-m-d H:i:s
            return $dt->format('Y-m-d H:i:s');
    }
}

function getIpAddress()
{
    $ip = request()->ip();

    # Replace localhost IP with a test IP (optional)
    if (in_array($ip, ['127.0.0.1', '::1'])) {
        $ip = '103.233.24.1';
    }
    return $ip;
}

function getAppUrl()
{
    return rtrim(config('app.url'), '/');
}


function formattedDateTime()
{
    # Get full date time + milliseconds
    $now = now(); # current time
    $micro = $now->format('u'); # microseconds like 893245
    $milliseconds = substr($micro, 0, 2); # first 2 digits = ms
    return $now->format('Ymd_His') . $milliseconds; # 20250426_15154289
}

function getSettingInfo()
{
    $setting = Setting::pluck('value', 'key') ?? [];
    $appUrl = rtrim(config('app.url'), '/');
    if (!empty($setting['company_logo'])) {
        $setting['company_logo'] = $appUrl . '/' . ltrim($setting['company_logo'], '/');
    }
    return $setting;
}

/* It creates an exception error
*
* @param exception The exception object
* @param type This is the type of exception.
*/
function createExceptionError($exception, $type, $function = null)
{
    Log::error("$type : $function => " . $exception->getMessage(), ['exception' => $exception]);
    try {
        $error_message = "Function $function : " . $exception->getMessage();
        $exception_first = ExceptionLog::where('status', ExceptionLog::PENDING)
            ->where('type', $type)->where('error', $error_message)
            ->where('line_number', $exception->getLine())
            ->latest()
            ->first();

        if (!$exception_first) {
            $data = [
                'status' => ExceptionLog::PENDING,
                'type' => $type,
                'title' => get_class($exception),
                'error' => $error_message,
                'file_name' => $exception->getFile(),
                'line_number' => $exception->getLine(),
                'full_error' => $exception,
                'type_count' => 1,
            ];
            ExceptionLog::create($data);
        } else {
            $exception_first->type_count += 1;
            $exception_first->error = "Function $function : " . $exception->getMessage();
            $exception_first->save();
        }
        return true;
    } catch (\Exception $e) {
        Log::error(COMMON_HELPER . " : createExceptionError => " . $e->getMessage(), ['exception' => $e]);
        return false;
    }
}

/**
 * It takes a role, and assigns all permissions to it, except for the ones that are in the array of
 * permissions that are denied
 *
 * @param role The name of the role.
 *
 */
function createNewRole($role)
{
    $permissions_ids = Permission::pluck('id')->toArray();

    # remove move old permission assign in $role  
    switch ($role->slug) {
        case RoleConst::SLUG_SUPER_ADMIN:
            $permissions_ids = Permission::pluck('id')->toArray();
            break;
        case RoleConst::SLUG_ADMIN:
            $permissions_ids = Permission::whereIn('permission', RoleConst::ADMIN_PERMISSION)->pluck('id')->toArray();
            break;
        case RoleConst::SLUG_USER:
            $permissions_ids = Permission::whereIn('permission', RoleConst::USER_PERMISSION)->pluck('id')->toArray();
            break;
        case RoleConst::SLUG_SERVICE_PROVIDER:
            $permissions_ids = Permission::whereIn('permission', RoleConst::SERVICE_PROVIDER_PERMISSION)->pluck('id')->toArray();
            break;
        default:
            $permissions_ids = [];
            break;
    }
    $role->permissions()->sync($permissions_ids);
    return $role;
}

function customizingResponseData($list)
{
    $data = [
        'data' => $list->items(),
        'current_page' => $list->currentPage(),
        'last_page' => $list->lastPage(),
        'per_page' => $list->perPage(),
        'total' => $list->total(),
        'from' => $list->firstItem(),
        'to' => $list->lastItem(),
    ];

    return $data;
}

// function readConstFileList(string $name, array $list = [], bool $position = false)
// {
//     # Define modules to check (class + constant name)
//     $optionalModules = [
//         ['class' => \Modules\AlertAndNotification\Constants\AlertNotificationConst::class, 'const' => "ALERT_AND_NOTIFICATION_" . $name . "_LIST"],
//         ['class' => \Modules\Clients\Constants\ClientConst::class, 'const' => "CLIENT_" . $name . "_LIST"],
//         ['class' => \Modules\Dashboard\Constants\DashboardConst::class, 'const' => "LEAD_" . $name . "_LIST"],
//         ['class' => \Modules\FollowUp\Constants\FollowUpConst::class, 'const' => "FOLLOW_UP_" . $name . "_LIST"],
//         ['class' => \Modules\Invoices\Constants\InvoiceConst::class, 'const' => "INVOICE_" . $name . "_LIST"],
//         ['class' => \Modules\Leads\Constants\LeadConst::class, 'const' => "LEAD_" . $name . "_LIST"],
//         ['class' => \Modules\ProductService\Constants\ProductServiceConst::class, 'const' => "PRODUCT_SERVICE_" . $name . "_LIST"],
//         ['class' => \Modules\Quotations\Constants\QuotationConst::class, 'const' => "QUOTATION_" . $name . "_LIST"],
//         ['class' => \Modules\SiteVisit\Constants\SiteVisitConst::class, 'const' => "SITE_VISIT_" . $name . "_LIST"],
//         ['class' => \Modules\RolePermission\Constants\RoleConst::class, 'const' => "ROLE_" . $name . "_LIST"],
//         ['class' => \Modules\Attendance\Constants\AttendanceConst::class, 'const' => "ATTENDANCE_" . $name . "_LIST"],
//         ['class' => \Modules\Targets\Constants\TargetConst::class, 'const' => "TARGET_" . $name . "_LIST"],
//     ];

//     foreach ($optionalModules as $module) {
//         if (class_exists($module['class']) && defined($module['class'] . '::' . $module['const'])) {
//             $list = array_merge($list, constant($module['class'] . '::' . $module['const']));
//         }
//     }

//     # Sort by position
//     if ($position) usort($list, function ($a, $b) {
//         return $a['position'] <=> $b['position'];
//     });

//     return $list;
// }

// function getConstHeaderList()
// {
//     $header_list = CommonConst::HEADER_MANAGE_LIST ?? [];

//     $prams = ["name" => "HEADER", "list" => $header_list, "position" => false];
//     return readConstFileList(...$prams);
// }

function createTableHeaderManage(string $slug)
{
    $list = CommonConst::HEADER_MANAGE_LIST;;
    # Find matching entry

    # $info = collect($list)->firstWhere(fn($item) => $item['slug'] == $slug);
    $info = collect($list)->firstWhere(function ($item) use ($slug) {
        return is_array($item) && isset($item['slug']) && $item['slug'] == $slug;
    });

    if (!$info) return false;

    $header = TableHeaderManage::updateOrCreate(
        [
            'user_id' => Auth::user()->uuid,
            'slug' => $slug,
        ],
        [
            'title' => $info['title'],
            'table' => $info['table'],
            'headers' => json_encode($info['headers'])
        ]
    );

    return $header;
}

function syncAllUserTableHeaderManage($slug)
{
    $list = CommonConst::HEADER_MANAGE_LIST;

    # Find matching entry

    $info = collect($list)->firstWhere(fn($item) => $item['slug'] === $slug);

    if (!$info) return false;

    $header = TableHeaderManage::where('slug', $slug)->update(
        ['title' => $info['title'], 'table' => $info['table'], 'headers' => json_encode($info['headers'])]
    );

    return $header;
}

function addStoragePermission($file_path)
{
    $storagePath = storage_path($file_path);

    # Ensure the directory exists with correct permissions
    if (!file_exists($storagePath)) {
        mkdir($storagePath, 0755, true); # Create the directory recursively
    }
}

function adminUserId()
{
    return User::withTrashed()->whereHas('user_role', function ($qu) {
        $qu->whereHas('role', function ($q) {
            $q->whereIn('slug', [RoleConst::SLUG_SUPER_ADMIN, RoleConst::SLUG_ADMIN]);
        });
    })->pluck('uuid')->toArray();
}

function applyFilteringUser_new($query, $types = ['created_by'], $user_view_id = null)
{
    $user = Auth::user();
    $columnMap = [
        'assigned_user' => 'assigned_user',
        'user_id' => 'user_id',
        'created_by' => 'created_by',
    ];

    # Resolve columns from provided types
    $columns = collect($types)->map(fn($type) => $columnMap[$type] ?? null)->filter()->unique();

    # Handle /user/view UUID from referer
    if (!empty($user_view_id)) {
        $query->where(function ($q) use ($columns, $user_view_id) {
            foreach ($columns as $column) {
                $q->orWhere($column, $user_view_id);
            }
        });
        return $query;
    }

    # Restrict for non-admins
    $roleSlugs = $user->roles()->pluck('slug')->toArray();
    if (!array_intersect($roleSlugs, [RoleConst::SLUG_SUPER_ADMIN, RoleConst::SLUG_ADMIN])) {
        $query->where(function ($q) use ($columns, $user) {
            foreach ($columns as $column) {
                $q->orWhere($column, $user->uuid);
            }
        });
    }

    return $query;
}

function getSendDataList($module, $type = null)
{
    $data = [];
    if ($module == CommonConst::ACCOUNT) {
        $user = request()->user();
        $request_device_info = $type == CommonConst::EMAIL ? addEmailDeviceInfo(request()) : addMessageDeviceInfo(request());
        $data = ['name' => $user->name, 'email' => $user->email, 'request_device_info' => $request_device_info];
    }
    return $data;
}

// function testNotificationInfo($lead_id)
// {
//     # ["name", "contact_person", "contact_person_role", "email", "phone", "address", "status", "source", "assigned_user", "note", "created_by", "last_updated_by", "client_id", "quotation_id"];
//     $variable_list = array_merge(LeadConst::LEAD_VARIABLE, ['id', 'created_by_uuid', 'last_updated_by_uuid', 'assigned_user_uuid']);
//     $lead = Lead::with(['creator', 'updater', 'assignedUser', 'client', 'quotations', 'status_info'])->findOrFail($lead_id);
//     $data = $lead->toArray();

//     # UUIDs of related users
//     $data['created_by_uuid'] = $lead->created_by; # UUID of creator
//     $data['last_updated_by_uuid'] = $lead->last_updated_by; # UUID of last updater
//     $data['assigned_user_uuid'] = $lead->assigned_user; # UUID of assigned user

//     # Human-readable names from relations
//     $data['created_by'] = $lead->creator?->name ?? ''; # Name of creator (User relation)
//     $data['last_updated_by'] = $lead->updater?->name ?? ''; # Name of last updater (User relation)
//     $data['assigned_user'] = $lead->assignedUser?->name ?? ''; # Name of assigned user (User relation)

//     # Status text from AdminControlConfig
//     $data['status'] = $lead->status_info?->status_text ?? $lead->status;

//     # Client name from Client model
//     $data['client_id'] = $lead->client?->name ?? ''; # Name of client

//     # Quotation numbers as comma-separated string
//     $data['quotation_id'] = $lead->quotations->count() > 0 ? implode(',', $lead->quotations->pluck('quotation_number')->toArray()) : ''; # Quotation numbers
//     $data['created_at'] = formatAccordingDateTime($lead->created_at, 'full_date_1');

//     # Filter only required fields from $data
//     $filteredData = [];
//     foreach ($variable_list as $key) {
//         $filteredData[$key] = $data[$key] ?? null;
//     }

//     return $filteredData;
// }


/**
 * It deletes all the roles for a user, then adds the roles passed in the  array
 *
 * @param roles An array of role uuids
 * @param user The user object
 */
function updateUserRoles($roles, $user)
{
    UserRole::where('user_id', $user->uuid)->delete();
    $new_roles = [];
    foreach ($roles  as $id) {
        $role = Role::where('id', $id)->first();
        if ($role) {
            UserRole::updateOrCreate(
                [
                    'user_id' => $user->uuid,
                    'role_id' => $role->id
                ]
            );
            $new_roles[] = $role->name;
        }
    }

    $user->assignRole($new_roles);
    $user->syncRoles($new_roles);
}

// function upload($file, $dir) {
//     // Initialize Cloudinary SDK
//     $cloudinary = new Cloudinary();

//     // Cloudinary settings
//     $cloud_name = env('CLOUDINARY_NAME', 'dc5hu9che');
//     $folder = $dir;
//     $media = $file;

//     // Image transformation settings
//     $width = 700;
//     $height = 800;
//     $quality = 'auto';
//     $fetch_format = 'auto';
//     $crop = 'scale';

//     // Upload the image with transformations (optimized version)
//     $optimal = $cloudinary->upload($media->getRealPath(), [
//         'folder'         => $folder,
//         'transformation' => [
//             'width'    => $width,
//             'height'   => $height,
//             'quality'  => $quality,
//             'fetch_format' => $fetch_format,
//             'crop'     => $crop
//         ]
//     ])->getSecurePath();

//     // Return the URL of the optimized image
//     return $optimal;

//     // Optionally, you can use a non-optimized upload for comparison
//     // $non_optimal = cloudinary()->upload($media->getRealPath(), [
//     //     'folder' => $folder
//     // ])->getSecurePath();

//     // Optimized image fetching
//     // If needed, you can build the optimized URL manually
//     // $slice = Str::afterLast($optimal, '/');
//     // $optimized_url = "https://res.cloudinary.com/{$cloud_name}/image/upload/w_{$width},h_{$height},c_{$crop}/{$folder}/{$slice}";
// }

function getDayName($dayNumber)
{
    switch ($dayNumber) {
        case 0:
            return 'Sunday';
        case 1:
            return 'Monday';
        case 2:
            return 'Tuesday';
        case 3:
            return 'Wednesday';
        case 4:
            return 'Thursday';
        case 5:
            return 'Friday';
        case 6:
            return 'Saturday';
        default:
            return null;
    }
}

function getDayNumber($day_name)
{
    switch ($day_name) {
        case 'Sunday':
            return 0;
        case 'Monday':
            return 1;
        case 'Tuesday':
            return 2;
        case 'Wednesday':
            return 3;
        case 'Thursday':
            return 4;
        case 'Friday':
            return 5;
        case 'Saturday':
            return 6;
        default:
            return null;
    }
}

function generateUniqueSlug()
{
    $slug = uniqid('order-');

    while (Order::where('slug', $slug)->exists()) {
        $slug = uniqid('order-');
    }
    return $slug;
}

function getDiscountPrice($amount, $discount = 0, $discount_type = StatusConst::PERCENTAGE)
{
    if ($discount_type == StatusConst::PERCENTAGE) {
        $discountedAmount = $amount - (($discount / 100) * $amount);
    } else {
        $discountedAmount = $amount - $discount;
    }
    return $discountedAmount > 0 ? round($discountedAmount) : 0;
}

// Calculate GST amount based on the tax type and percentage
function gstAmount($amount, $tax_percentage = 0, $tax_type = StatusConst::EXCLUSIVE)
{
    if ($tax_type === StatusConst::INCLUSIVE) {
        return round($amount * (($tax_percentage / 100) + 1) - ($amount));
    } else {
        return round(($amount * (($tax_percentage / 100) + 1)) - ($amount));
    }
}

function postCodeAddress($pin_code)
{
    $response = Http::get("https://api.postalpincode.in/pincode/" . $pin_code);
    return $response;
}

/**
 * Get location details from an IP address using ip-api.com (Free).
 *
 * @param string|null $ipAddress The IP address to lookup (use null for client IP)
 * @return array|null Returns an array with location data or null if not found
 */
function getLocationDetails($ipAddress = null)
{
    $info = null;
    if (!$ipAddress) {
        $ipAddress = request()->ip();
    }

    $response = Http::get("http://ip-api.com/json/{$ipAddress}");

    if ($response->successful()) {
        $data = $response->json();

        if ($data['status'] === 'success') {
            $info = (object)[
                'country' => $data['country'],
                'state' => $data['regionName'],
                'city' => $data['city'],
                'latitude' => $data['lat'],
                'longitude' => $data['lon'],
            ];
        }
    }
    return $info;
}

function convertPercentageToRating($percentage)
{
    if ($percentage < 0) {
        $percentage = 0;
    } elseif ($percentage > 100) {
        $percentage = 100;
    }

    $rating = ($percentage / 100) * 5;

    return round($rating, 1);
}

function storeCreateDefault($user_id)
{
    $create = [
        'user_id' => $user_id,
        'status' => StatusConst::ACTIVE,
    ];

    $store = Store::create($create);
}

