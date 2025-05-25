<?php

namespace App\Jobs;

use App\Constants\CommonConst;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailSend;
use Throwable;

class EmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $emailPriority;
    protected ?string $mailLogId;
    protected array $userList;
    protected array $emailContentInfo;
    protected array $additionalInfo;

    public function __construct(?string $mailLogId = null, array $userList = [], array $emailContentInfo = [], array $additionalInfo = [])
    {
        $this->mailLogId = $mailLogId;
        $this->userList = $userList;
        $this->emailContentInfo = $emailContentInfo;
        $this->additionalInfo = $additionalInfo;
        $this->emailPriority = CommonConst::HIGH;
    }

    public function handle(): void
    {
        i("=== EmailJob handle ===", 0);
        if ($this->mailLogId) {
            $logs = NotificationLog::where('id', $this->mailLogId)->get();
            i("=== EmailJob handle ===", 1);
            foreach ($logs as $log) {
                $this->processAndSend($log);
            }
        } elseif (!empty($this->userList) || !empty($this->emailContentInfo)) {
            i("=== EmailJob handle ===", 2);
            $this->createAndSendNewEmail();
        } else {
            i("=== EmailJob handle ===", 3);
            $logs = NotificationLog::where('status', CommonConst::PENDING)
                ->where('priority', $this->emailPriority)
                ->limit(20)
                ->get();

            foreach ($logs as $log) {
                $this->processAndSend($log);
            }
        }
    }

    protected function createAndSendNewEmail(): void
    {
        foreach ($this->userList as $key => $user) {
            i("=== EmailJob createAndSendNewEmail ===", 201);
            $this->additionalInfo['receiver_id'] = $user['uuid'];
            $this->additionalInfo['receiver_contact'] = $user['email'];
            $this->additionalInfo['user_name'] = $user['name'];

            $userName = $user['name'];
            $notificationTypeId = $this->additionalInfo['notification_type_id'] ?? null;
            $receiverContact = $this->additionalInfo['receiver_contact'];
            $isNotification = $this->additionalInfo['is_notification'] ?? true;
            if ($notificationTypeId) {
                $content = makeMessageContent($this->emailContentInfo, $notificationTypeId, CommonConst::EMAIL);
            } else {
                $content = (object)[
                    "status" => true,
                    "hidden_pre_header" => "",
                    "subject" => $this->additionalInfo['subject'] . $userName,
                    "priority" => CommonConst::HIGH,
                    "whats_app_message" => $this->emailContentInfo['message'],
                ];
                $this->additionalInfo['subject'] =  $this->additionalInfo['subject'] . $userName;
            }

            if ($content->status) {
                $this->additionalInfo['hidden_pre_header'] = $content->hidden_pre_header;

                $logData = [
                    'receiver_contact' => $receiverContact,
                    'subject' => $content->subject,
                    'content' => $content->content,
                    'priority' => $content->priority,
                    'status' => CommonConst::PENDING,
                    'notification_type_id' => $notificationTypeId,
                    'receiver_id' => $this->additionalInfo['receiver_id'] ?? null,
                    'section_type' => CommonConst::EMAIL,
                    'is_notification' => $isNotification,
                    'email_body' => json_encode($this->emailContentInfo),
                    'additional_info' => json_encode($this->additionalInfo),
                    'sender_id' => $this->additionalInfo['sender_id'] ?? null,
                ];

                $log = NotificationLog::create($logData);
                i("=== EmailJob createAndSendNewEmail ===",  $log->receiver_contact);
                if ($log && !$isNotification) {
                    $this->sendEmail($receiverContact, $content, $this->additionalInfo, $log);
                } else {
                    $log->status = CommonConst::SUCCESS;
                    i("=== EmailJob createAndSendNewEmail Else ===",  $log->status);
                    $log->save();
                }
            }
        }
    }

    protected function processAndSend(NotificationLog $log): void
    {
        $additionalInfo = is_array($log->additional_info) ? $log->additional_info : json_decode($log->additional_info, true);
        $email_body = is_array($log->email_body) ? $log->email_body : json_decode($log->email_body, true);
        $content = makeMessageContent($email_body, $log->notification_type_id, CommonConst::EMAIL);

        if (!$content->status) return;

        $this->sendEmail($log->receiver_contact, $content, $additionalInfo, $log);
    }

    protected function sendEmail(string $to, object $content, array $info, NotificationLog $log): void
    {
        i("=== EmailJob sendEmail ===");
        try {
            $emailData = [
                'subject' => $content->subject,
                'attachment_path' => $info['attachment_path'] ?? null,
                'attachment_original_name' => $info['attachment_original_name'] ?? null,
                'hidden_pre_header' => $content->hidden_pre_header,
                'content' => $content->simple_content,
            ];
            i("=== EmailJob sendEmail ===", json_encode($emailData), 1);
            Mail::to($to)->send(new MailSend($emailData));
            $log->status = CommonConst::SUCCESS;
        } catch (\Exception $e) {
            createExceptionError($e, 'EmailJob', __FUNCTION__);
            $log->status = CommonConst::FAILED;
            $log->message = $e->getMessage();
            i("=== EmailJob sendEmail FAILED ===", json_encode($log->message), 1);
        }

        $log->save();
    }

    public function failed(Throwable $exception): void
    {
        er("=== EmailJob Failed: " . $exception->getMessage(), 1);
    }
}

