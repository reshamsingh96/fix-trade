<?php

use App\Constants\CommonConst;
use App\Constants\RoleConst;
use Carbon\Carbon;
use App\Mail\MailSend;
use App\Models\NotificationLog;
use App\Models\NotificationTemplateSection;
use App\Models\NotificationType;
use App\Models\NotificationVariable;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Stevebauman\Location\Facades\Location;

const EMAIL_HELPER = 'Helpers / Email Helper';

function adminAndSuperAdminUserList()
{
    return User::withTrashed()->whereHas('user_role', function ($qu) {
        $qu->whereHas('role', function ($q) {
            $q->whereIn('slug', [RoleConst::SLUG_SUPER_ADMIN, RoleConst::SLUG_ADMIN]);
        });
    })->select('uuid', 'name', 'user_name', 'email', 'country_code', 'phone')->get()->toArray();
}

function emailUserInfo($user_id = null)
{
    $list = [];
    if ($user_id) {
        $list =  User::withTrashed()->where('uuid', $user_id)->select('uuid', 'name', 'user_name', 'email', 'country_code', 'phone')->get()->toArray() ?? [];
    }
    return $list;
}

function addEmailDeviceInfo($request)
{
    $browser = $request->header('User-Agent');
    $ip = getIpAddress();
    $location = Location::get($ip);

    $country = $location?->countryName ?? null;
    $state   = $location?->regionName ?? null;
    $city    = $location?->cityName ?? null;

    $time = Carbon::now()->format('M, d Y H:i:s A');

    $additional_info = "<strong style='font-size: 20px;'>Device Information : </strong><br>";
    $additional_info .= "<strong>When</strong> - $time<br>";
    $additional_info .= "<strong>IP Address</strong> - $ip<br>";
    $additional_info .= "<strong>Device Type</strong> - $browser<br>";

    // Conditionally add location data if available
    if ($country || $state || $city) {
        $additional_info .= "<strong>Location</strong> - ";
        $location_parts = [];

        if ($city) {
            $location_parts[] = $city;
        }
        if ($state) {
            $location_parts[] = $state;
        }
        if ($country) {
            $location_parts[] = $country;
        }

        $additional_info .= implode(', ', $location_parts) . "<br>";
    }

    return $additional_info;
}

function addMessageDeviceInfo($request)
{
    $browser = $request->header('User-Agent');
    $ip = getIpAddress();
    $location = Location::get($ip);

    $country = $location?->countryName ?? null;
    $state   = $location?->regionName ?? null;
    $city    = $location?->cityName ?? null;

    $time = Carbon::now()->format('M, d Y H:i:s A');

    $additional_info = " Device Information : ";
    $additional_info .= "When - $time";
    $additional_info .= "IP Address - $ip";
    $additional_info .= "Device Type - $browser";

    if ($country || $state || $city) {
        $additional_info .= "Location - ";
        $location_parts = [];

        if ($city) {
            $location_parts[] = $city;
        }
        if ($state) {
            $location_parts[] = $state;
        }
        if ($country) {
            $location_parts[] = $country;
        }

        $additional_info .= implode(', ', $location_parts);
    }

    return $additional_info;
}

function makeMessageContent($replacements, $notification_type_id, $type)
{
    try {
        $template = NotificationTemplateSection::where('notification_type_id', $notification_type_id)->first();
        if (!$template) {
            return (object)['status' => false, "message" => "Email Template Not Found!"];
        }

        $setting = getSettingInfo();
        $replacements['company_name'] = $setting['company_name'] ?? 'No Name';

        $templateFields = [
            CommonConst::EMAIL => 'email_body',
            CommonConst::WHATSAPP => 'whats_app_message',
            CommonConst::SMS => 'sms_message',
            CommonConst::BELL_NOTIFICATION => 'bell_notification_message',
        ];
        $responseKeys = [
            CommonConst::EMAIL => ['content', 'simple_content'],
            CommonConst::WHATSAPP => 'whats_app_message',
            CommonConst::SMS => 'sms_message',
            CommonConst::BELL_NOTIFICATION => 'bell_notification_message',
        ];

        $content = $template->{$templateFields[$type] ?? 'email_body'};
        $priority = $template->priority;
        $hidden_pre_header = $template->hidden_pre_header;
        $subject = $template->email_subject;

        if ($type === CommonConst::EMAIL && $template->is_enable === 'Disable') {
            return (object)['status' => false, "message" => 'Template is disabled. So not email send.'];
        }

        $variables = NotificationVariable::where('notification_type_id', $notification_type_id)->pluck('variables');
        foreach ($variables as $variable) {
            if (isset($replacements[$variable])) {
                $value = $replacements[$variable];
                $content = str_replace("[[**$variable**]]", $value, $content);
                $subject = str_replace("[[**$variable**]]", $value, $subject);
                $content = str_replace("[[***$variable***]]", "<a href='$value'>Link</a>", $content);
                $hidden_pre_header = str_replace("[[**$variable**]]", $value, $hidden_pre_header);
            }
        }

        $response = [
            'status' => true,
            'hidden_pre_header' => $hidden_pre_header,
            'subject' => $subject,
            'priority' => $priority,
        ];

        switch ($type) {
            case CommonConst::EMAIL:
                $response['content'] = (string)view('email.email_content', compact('content', 'hidden_pre_header', 'setting'));
                $response['simple_content'] = $content;
                break;
            case CommonConst::WHATSAPP:
            case CommonConst::SMS:
            case CommonConst::BELL_NOTIFICATION:
                $key = $responseKeys[$type];
                $response[$key] = $content;
                break;
        }

        return (object)$response;
    } catch (\Exception $e) {
        createExceptionError($e, EMAIL_HELPER, __FUNCTION__);
        return (object)['status' => false, "message" => $e->getMessage()];
    }
}

# Helper method to send emails throughout the application
function commonSendEmailFun($email_content_info, $additional_info)
{
    $notification_type_id = $additional_info['notification_type_id'];
    $receiver_contact = $additional_info['receiver_contact'];
    $is_notification = $additional_info['is_notification'] ?? true;

    $mailContent = makeMessageContent($email_content_info, $notification_type_id, CommonConst::EMAIL);
    if ($mailContent->status) {
        $additional_info['hidden_pre_header'] =  $mailContent->hidden_pre_header;
        try {
            $receiver_id = $additional_info['receiver_id'];
            $logData = [
                'receiver_contact' => $receiver_contact,
                'subject' => $mailContent->subject,
                'content' => $mailContent->content,
                'priority' => $mailContent->priority,
                'status' => CommonConst::PENDING,
                'notification_type_id' => $notification_type_id,
                'receiver_id' => $receiver_id,
                'section_type' => CommonConst::EMAIL,
                'is_notification' => $is_notification,
                'email_body' => json_encode($email_content_info),
                'additional_info' => json_encode($additional_info),
                'sender_id' => $additional_info['sender_id'] ?? null,
            ];

            $email_log = NotificationLog::create($logData);

            if ($email_log) {
                try {
                    if (!$is_notification) {
                        $email_info = [
                            'subject' => $mailContent->subject,
                            'attachment_path' => $additional_info['attachment_path'] ?? null,
                            'attachment_original_name' => $additional_info['attachment_original_name'] ?? null,
                            'hidden_pre_header' => $mailContent->hidden_pre_header,
                            'content' => $mailContent->simple_content,
                        ];
                        Mail::to($receiver_contact)->send(new MailSend($email_info));
                    }
                    $email_log->status = CommonConst::SUCCESS;
                    $email_log->save();
                } catch (\Exception $e) {
                    createExceptionError($e, COMMON_HELPER, __FUNCTION__);
                    $email_log->status = CommonConst::FAILED;
                    $email_log->message = $e->getMessage();
                    $email_log->save();
                    return (object)['status' => false, 'message' => $e->getMessage(), "email_log" => $email_log];
                }
                return (object)['status' => true, "email_log" => $email_log];
            }
        } catch (\Exception $e) {
            createExceptionError($e, EMAIL_HELPER, __FUNCTION__);
            return (object)['status' => false, "message" => $e->getMessage()];
        }
    }
    return (object)['status' => false, "message" => $mailContent->message];
}

# if user successfully login then add login log
function adminAddLoginUserLog($user, $request)
{
    try {
        $notification_type = NotificationType::where('type_key', CommonConst::ACCOUNT_LOGIN)->select('id', 'title')->first();
        $request_device_info = addEmailDeviceInfo($request);

        $adminList = adminAndSuperAdminUserList();

        $email_content_info = [
            'name' => $user->name,
            'request_device_info' => $request_device_info,
        ];

        // $additional_info = [
        //     'notification_type_id' => $notification_type->id,
        //     'sender_id' => $user->uuid,
        //     'attachment_path' => null,
        //     'attachment_original_name' => null,
        //     "is_notification" => false # Gmail account Send Mail
        // ];
        // $log_id = null;
        // EmailJob::dispatch($log_id, $adminList, $email_content_info, $additional_info);
        // return;

        $not_send = false;
        $list = [];
        foreach ($adminList as $key => $admin) {
            $additional_info = [
                'receiver_id' => $admin['uuid'],
                'receiver_contact' => $admin['email'],
                'notification_type_id' => $notification_type->id,
                'sender_id' => $user->uuid,
                'attachment_path' => null,
                'attachment_original_name' => null,
                "is_notification" => true # Only Notification Show not Gmail account Send Mail
            ];
            if ($admin['uuid'] != $user->uuid) {
                $info = commonSendEmailFun($email_content_info, $additional_info);
                if ($info->status == false) {
                    $not_send = true;
                    $list[] = $info->message;
                }
            }
        }

        if ($not_send) return (object)['status' => false, "list" => $list];
        return (object)['status' => true, "message" => "Mail Send Successfully"];

        return;
    } catch (\Exception $e) {
        createExceptionError($e, EMAIL_HELPER, __FUNCTION__);
        return (object)['status' => false, "message" => $e->getMessage()];
    }
}
