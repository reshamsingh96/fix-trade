<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Constants\CommonConst;
use App\Constants\RoleConst;
use App\Events\NotificationMessage;
use App\Http\Controllers\Controller;
use App\Models\NotificationCategory;
use App\Models\NotificationLog;
use App\Models\NotificationTemplateSection;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WhatsAppController extends Controller
{
    const CONTROLLER_NAME = "Whats App Controller";

    protected $referer;
    protected $login_user;

    public function __construct()
    {
        $this->login_user = request()->user() ?? Auth::user() ?? null;
    }

    public function whatsAppNotificationCount(Request $request)
    {
        try {
            $user = $this->login_user;
            $roleSlugs = $user->roles()->pluck('slug')->toArray();

            # Base query for email notifications
            $query = NotificationLog::query()
                ->where('section_type', CommonConst::WHATSAPP)
                ->where('is_delete', 0)
                ->with(['sender', 'notification_type']);

            # If not admin/super admin, filter by receiver
            if (!array_intersect($roleSlugs, [RoleConst::SLUG_SUPER_ADMIN, RoleConst::SLUG_ADMIN])) {
                $query->where('receiver_id', $user->uuid);
            }

            # Unread notifications: where user's UUID is NOT in showing_user_ids
            $unreadCount = $query->where(function ($q) use ($user) {
                $q->whereNull('showing_user_ids')
                    ->orWhereRaw("NOT showing_user_ids::jsonb @> '\"{$user->uuid}\"'");
            })->count();
            if ($request->type == 'count') return $unreadCount;
            return $this->actionSuccess("Unread email notification count retrieved successfully.", [
                'un_read' => $unreadCount
            ]);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function whatsAppLatestFiveNotificationList(Request $request)
    {
        try {
            $user = $this->login_user;
            $isAdmin = collect($user->roles()->pluck('slug'))->intersect([RoleConst::SLUG_SUPER_ADMIN, RoleConst::SLUG_ADMIN,])->isNotEmpty();

            $query = NotificationLog::query()
                ->where('section_type', CommonConst::WHATSAPP)
                ->with(['sender', 'notification_type'])
                ->latest()
                ->limit(5);

            if (!$isAdmin) {
                $query->where('receiver_id', $user->uuid)
                    ->where('is_delete', 0);
            }
 
            $query->with('sender','receiver');
            $list = $query->get();
            if ($request->type == 'list') return $list;
            return $this->actionSuccess('WhatsApp latest five notifications retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function whatsAppMarkAllReadOrUnRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_read' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed(true, $validator->errors());
        }

        try {
            $user = $this->login_user;
            $isRead = $request->is_read;

            $isAdmin = collect($user->roles()->pluck('slug'))->intersect([RoleConst::SLUG_SUPER_ADMIN, RoleConst::SLUG_ADMIN,])->isNotEmpty();

            $query = NotificationLog::query()->where('section_type', CommonConst::WHATSAPP);

            if (!$isAdmin) {
                $query->where('receiver_id', $user->uuid)->where('is_delete', 0);
            }
            $notifications = $query->where('is_delete', 0)->get();

            foreach ($notifications as $log) {
                $ids = $log->showing_user_ids ?? [];

                if ($isRead) {
                    # Mark as read: Add user ID if not already present
                    if (!in_array($user->uuid, $ids)) {
                        $ids[] = $user->uuid;
                        $log->showing_user_ids = array_unique($ids);
                        $log->save();
                    }
                } else {
                    # Mark as unread: Remove user ID if present
                    if (in_array($user->uuid, $ids)) {
                        $updatedIds = array_values(array_diff($ids, [$user->uuid]));
                        $log->showing_user_ids = $updatedIds;
                        $log->save();
                    }
                }
            }

            $message = $isRead ? "All WhatsApp notifications marked as read." : "All WhatsApp notifications marked as unread.";
            return $this->actionSuccess($message);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function whatsAppIsReadNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|uuid',
            'is_read' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed(true, $validator->errors());
        }

        try {
            $userId = $this->login_user->uuid;

            $log = NotificationLog::where('id', $request->notification_id)
                ->where('section_type', CommonConst::WHATSAPP)
                ->first();

            if (!$log) {
                return $this->actionFailure("Notification not found or access denied.");
            }

            # Only update if user hasn't already marked it as read
            $showingUserIds = $log->showing_user_ids ?? [];
            if (!in_array($userId, $showingUserIds)) {
                $log->showing_user_ids = array_unique([...$showingUserIds, $userId]);
                $log->save();
            }

            return $this->actionSuccess("Status updated successfully.", $log);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    # Notification Log Function Api Start -->
    public function whatsAppLogList(Request $request)
    {
        try {
            $params = [
                'per_page'     => $request->input('per_page', 100),
                'select_email' => $request->input('select_email'),
                'sort_key'     => $request->input('sortBy'),
                'sort_order'   => $request->input('orderBy'),
                'search'       => $request->input('search'),
                'status'       => $request->input('status'),
            ];

            $items_list = $this->_whatsAppLogList(...$params);
            return  $this->actionSuccess('Get WhatsApp Log List Successfully', customizingResponseData($items_list));
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    private function _whatsAppLogList(int $per_page, ?string $select_email = null, ?string $sort_key = null, ?string $sort_order = null, ?string $search = null, ?string $status = null)
    {
        $user = $this->login_user;
        $roleSlugs = $user->roles()->pluck('slug')->toArray();

        $query = NotificationLog::query()
            ->where('section_type', CommonConst::WHATSAPP)
            ->search($search)
            ->with(['sender', 'notification_type']);
        if ($status) $query->where('status', $status);
        if (!array_intersect($roleSlugs, [RoleConst::SLUG_SUPER_ADMIN, RoleConst::SLUG_ADMIN])) {
            $query->where('receiver_id', $user->uuid)
                ->where('is_delete', 0);
        } elseif ($select_email) {
            $query->where('receiver_id', $select_email);
        }

        # Optional: Custom behavior based on route_name
        if (request()->query('route_name') === 'whatsapp-reachout-log') {
            $query->whereNotNull('module_id')->whereNull('notification_type_id');
        }

        if ($sort_key && $sort_order) {
            $query->orderBy($sort_key, $sort_order);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $query->with('sender','receiver');
        return $query->paginate($per_page);
    }

    public function whatsAppUpdateReadStatusUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationFailed($validator->fails(), $validator->errors());
        }
        try {
            $userId = $this->login_user->uuid;
            $log = NotificationLog::where('id', $request->id)
                ->where('section_type', CommonConst::WHATSAPP)
                ->first();

            if (!$log) {
                return $this->actionFailure("Notification not found or access denied.");
            }

            # Only update if user hasn't already marked it as read
            $showingUserIds = $log->showing_user_ids ?? [];
            if (!in_array($userId, $showingUserIds)) {
                $log->showing_user_ids = array_unique([...$showingUserIds, $userId]);
                $log->save();
            }
            try {
                event(new NotificationMessage("", $userId, CommonConst::WHATSAPP));
            } catch (\Exception $e) {
                createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            }
            return $this->actionSuccess("Status updated successfully.", $log);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function whatsAppStatusUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_log_id' => 'required|exists:notification_logs,id',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $log = NotificationLog::where('id', $request->notification_log_id)->first();
            if (!$log) return $this->actionFailure('Notification Log not Found!');
            $log->status = $request->status;
            $log->save();
            DB::commit();
            return $this->actionSuccess('Notification Log Status updated successfully.', $log);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function whatsAppDeleteNotification(Request $request)
    {
        try {
            # $this->login_user
            $emailLog = NotificationLog::where('id', $request->notification_type_id)->first();
            if (!$emailLog) return $this->actionFailure("Email log not found!");
            $emailLog->is_delete = true;
            $emailLog->save();
            return $this->actionFailure('Email Notification Soft Delete Successfully', $emailLog);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    # Notification Utilities Function Api Start -->
    /**
     * It gets all the email categories and their types.
     * @group Mail Api
     * @param Request request The request object.
     * @authenticated
     * @return JSON response
     */
    public function whatsAppCategoryList()
    {
        try {
            $list = NotificationCategory::with('notification_types')->get();
            return $this->actionSuccess("Whats App Category list get Successfully", $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    /**
     * Create or update an email template.
     * 
     * @group Mail Api
     * @param Request $request The request object.
     * @authenticated
     * @return JSON response
     */
    public function whatsAppCreateUpdateTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|exists:notification_template_sections,id',
            'email_subject' => 'required|string',
            'whats_app_message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed(true, $validator->errors());
        }

        try {
            $data = $this->_whatsAppCreateUpdateTemplate($request->only(['id', 'email_subject', 'whats_app_message']));

            return $this->actionSuccess("Record saved successfully", $data);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure("Creation failed");
        }
    }


    /**
     * Update or create a new WhatsApp email template.
     *
     * @param array $data The template data.
     * @return \App\Models\NotificationTemplateSection
     */
    private function _whatsAppCreateUpdateTemplate(array $data)
    {
        $whats_app_message = str_replace(['amp;', 'amp'], '', nl2br(e($data['whats_app_message'])));
        return NotificationTemplateSection::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'email_subject' => $data['email_subject'],
                'whats_app_message' => $whats_app_message ?? $data['whats_app_message'],
            ]
        );
    }

    /**
     * It takes a request, gets the user, and then passes the user's name and email to the
     * `makeMessageContent` function
     * @group Mail Api
     * @param Request request The request object
     * @authenticated
     */
    public function whatsAppPreview(Request $request)
    {
        try {
            $category_name = NotificationCategory::whereHas('notification_types', function ($qu) use ($request) {
                $qu->where('id', $request->notification_type_id);
            })->pluck('category')->first();

            $body_data = getSendDataList($category_name);
            $content = makeMessageContent($body_data, $request->notification_type_id, CommonConst::WHATSAPP);

            if ($content->status) {
                return $this->actionSuccess("Whats App data get Successfully", $content);
            }
            return $this->actionFailure($content->message);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    /**
     * It takes the email template type id, creates the email content, and sends it to the test
     * receiver
     * @group Mail Api
     * @param Request request The request object
     * @authenticated
     */
    public function whatsAppSendNotification(Request $request)
    {
        try {
            $user = Auth::user();
            $receiver_contact = $user->phone; #    
            $category_name = NotificationCategory::whereHas('notification_types', function ($qu) use ($request) {
                $qu->where('id', $request->notification_type_id);
            })->pluck('category')->first();

            $body_data = getSendDataList($category_name);
            $content = makeMessageContent($body_data, $request->notification_type_id, CommonConst::WHATSAPP);

            if ($content->status) {
                $receiver_id = $user->uuid;
                $sender_id = $user->uuid;
                $logData = [
                    'receiver_contact' => $receiver_contact,
                    'subject' => $content->subject,
                    'content' => $content->whats_app_message,
                    'priority' => $content->priority,
                    'status' => CommonConst::PENDING,
                    'notification_type_id' => $request->notification_type_id,
                    'receiver_id' => $receiver_id,
                    'section_type' => CommonConst::WHATSAPP,
                    "is_notification" => false,
                    'email_body' => json_encode($user),
                    'sender_id' => $sender_id ?? null,

                ];

                $emailLog = NotificationLog::create($logData);

                # 1. Generate PDF and save to public storage
                // $pdf = PDF::loadView('pdf.test_pdf', ['data' => []]);
                // $filename = 'attachment_' . time() . '.pdf';
                // $path = 'email_attachments/' . $filename;
                // Storage::disk('public')->put($path, $pdf->output());

                # 2. Get public URL for media
                $fileUrl = ""; #  asset('storage/' . $path);
                $fileCaption = ""; # "test file send" ?? null;
                $extension = ""; // Document , Image 
                # Send message via
                $userName = trim($user->name);
                $whatsAppMessage = str_replace(['<br>', '<br/>', '<br />'], "\n", $content->whats_app_message);
                $response = (new WhatsAppService())->sendMediaMessage($userName, $receiver_contact, $whatsAppMessage, $fileUrl, $fileCaption, $extension);
                if ($response->status) {
                    $emailLog->status = CommonConst::SUCCESS;
                    $emailLog->save();
                } else {
                    $emailLog->status = CommonConst::FAILED;
                    $emailLog->message = $response->message;
                    $emailLog->save();
                }
                try {
                    event(new NotificationMessage($emailLog->subject, $receiver_id, CommonConst::WHATSAPP));
                } catch (\Exception $e) {
                    createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
                }

                return $emailLog->status == CommonConst::SUCCESS ? $this->actionSuccess('Mail Send Successfully', $emailLog) : $this->actionFailure($emailLog->message, $emailLog);
            }
            return $this->actionFailure($content->message);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }
}
