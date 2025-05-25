<?php

namespace App\Database\Seeders;

use App\Constants\CommonConst;
use App\Models\NotificationCategory;
use App\Models\NotificationTemplateSection;
use App\Models\NotificationType;
use App\Models\NotificationVariable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NotificationCategory::query()->delete();
        NotificationType::query()->delete();
        NotificationTemplateSection::query()->delete();
        NotificationVariable::query()->delete();
        $email_List = [
            [
                'category' => CommonConst::ACCOUNT,
                'type' => [
                    [
                        'title' => "Account Login",
                        'type_key' => CommonConst::ACCOUNT_LOGIN,
                        'description' => 'Description of Account Login Email.',
                        'template' => [
                            'hidden_pre_header' => 'Greetings from [[**company_name**]]',
                            'email_subject' => "Account Login Email",
                            'email_body' => "<h2>[[**name**]] , Did You Login ?</h2><p>We noticed the login for your [[**company_name**]] account was recently. If this was you, you can safely disregard this email.</p><p>[[**request_device_info**]]</p>",
                            "whats_app_message" => "[[**name**]], was this your login attempt? [[**request_device_info**]]",
                            "sms_message" => "Hi [[**name**]], we noticed a login attempt. [[**request_device_info**]]",
                            "bell_notification_message" => "Login detected for [[**name**]]. [[**request_device_info**]]",
                            'priority' => CommonConst::HIGH,
                            'is_enable' => CommonConst::REQUIRED
                        ],
                        'variables' => ["company_name", "name", "request_device_info"],
                    ],
                    [
                        'title' => "Password Reset",
                        'type_key' => CommonConst::PASSWORD_RESET,
                        'description' => 'Description of Password Reset.',
                        'template' => [
                            'hidden_pre_header' => 'Password change Notification',
                            'email_subject' => "Account Password Changed!",
                            'email_body' => "<center><h2> [[**name**]] , Did You change your password ?</h2></center><p>We noticed the password for your [[**company_name**]] account was recently changed.If this was you, you can safely disregard this email.</p><p>[[**request_device_info**]]</p>",
                            "whats_app_message" => "[[**name**]], your password was recently changed. [[**request_device_info**]]",
                            "sms_message" => "Hi [[**name**]], we detected a password change. [[**request_device_info**]]",
                            "bell_notification_message" => "Password update noticed for [[**name**]]. [[**request_device_info**]]",
                            'priority' => CommonConst::HIGH,
                            'is_enable' => CommonConst::REQUIRED
                        ],
                        'variables' => ["company_name", "name", "email", "phone", "request_device_info"],
                    ],
                    [
                        'title' => "Forget Password",
                        'type_key' => CommonConst::FORGET_PASSWORD,
                        'description' => 'Description of Forget Password.',
                        'template' => [
                            'hidden_pre_header' => 'Greetings from [[**company_name**]]',
                            'email_subject' => "Forgot Password",
                            'email_body' => "<p>Trouble signing in [[**name**]]?</p><p>Resetting your password is easy.</p><p>Just click this [[***reset_link***]] and follow the instructions. We?ll have you up and running in no time.</p><p>If the link above don't work, please paste the below URL into your web browser.</p>[[**reset_link**]]<p>If you did not make this request then please ignore this email.</p><p>[[**request_device_info**]]</p>",
                            "whats_app_message" => "[[**name**]], click here to reset your password: [[**reset_link**]]",
                            "sms_message" => "Reset your [[**company_name**]] password: [[**reset_link**]]",
                            "bell_notification_message" => "Password reset link sent to [[**email**]]",
                            'priority' => CommonConst::HIGH,
                            'is_enable' => CommonConst::REQUIRED
                        ],
                        'variables' => ["company_name", "name", "email", "reset_link", "copy_reset_link", "request_device_info"],
                    ],
                    [
                        'title' => "Register User Mail",
                        'type_key' => CommonConst::REGISTER_USER,
                        'description' => 'Description of User Register User mail',
                        'template' => [
                            'hidden_pre_header' => 'Greetings from [[**company_name**]]',
                            'email_subject' => "Register Notification",
                            'email_body' => "<center> [[**name**]]</center><p>New User Register Notification </p><br>User Name is</strong> - [[**name**]] <br>Phone Ref is</strong> - [[**user_phone**]] <br>Email is</strong> - [[**user_email**]] <br>",
                            "whats_app_message" => "Welcome [[**name**]]! Your registration is complete.",
                            "sms_message" => "Hi [[**name**]], you’re registered with [[**company_name**]].",
                            "bell_notification_message" => "New user registered: [[**name**]]",
                            'priority' => CommonConst::HIGH,
                            'is_enable' => CommonConst::ENABLE
                        ],
                        'variables' => ["company_name", "name", "user_phone", "user_email"],
                    ],
                    [
                        'title' => "Password Update Mail",
                        'type_key' => CommonConst::UPDATE_PASSWORD,
                        'description' => 'Description of User Password Update mail',
                        'template' => [
                            'hidden_pre_header' => 'Greetings from [[**company_name**]]',
                            'email_subject' => "Password Update Notification",
                            'email_body' => "<center> [[**name**]]</center><p>Password Update Notification </p> <br>Your password has been updated Successfully",
                            "whats_app_message" => "[[**name**]], your password has been updated successfully.",
                            "sms_message" => "Hi [[**name**]], your password was updated.",
                            "bell_notification_message" => "Password successfully updated for [[**name**]].",
                            'priority' => CommonConst::HIGH,
                            'is_enable' => CommonConst::ENABLE
                        ],
                        'variables' => ["company_name", "name"],
                    ],
                    [
                        'title' => "User Email Verify Send Otp",
                        'type_key' => CommonConst::EMAIL_VERIFY_SEND_OTP,
                        'description' => 'Description of User Login Email verify Send Otp.',
                        'template' => [
                            'hidden_pre_header' => 'Greetings from [[**company_name**]]',
                            'email_subject' => "Email Verify Send Otp",
                            'email_body' => "<center>Hey [[**name**]]</center>, <br><p> Your Email verification otp is [[**otp**]]</p>",
                            "whats_app_message" => "Hi [[**name**]], your OTP is [[**otp**]].",
                            "sms_message" => "Your [[**company_name**]] OTP is [[**otp**]]",
                            "bell_notification_message" => "OTP sent to [[**name**]] for email verification.",
                            'priority' => CommonConst::HIGH,
                            'is_enable' => CommonConst::REQUIRED
                        ],
                        'variables' => ["company_name", "name", "otp"],
                    ],
                ],
            ],
        ];

        $prams = ["name" => "EMAIL_TEMPLATE", "list" => $email_List, "position" => false];
        $email_List = $email_List; //readConstFileList(...$prams);

        // Count total entries for progress bar
        $total = collect($email_List)->sum(fn($cat) => count($cat['type']));
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        foreach ($email_List as $categoryData) {
            $category = NotificationCategory::updateOrCreate(['category' => $categoryData['category']]);

            foreach ($categoryData['type'] as $type) {
                $emailType = NotificationType::updateOrCreate(
                    ['category_id' => $category->id, 'type_key' => $type['type_key']],
                    [
                        'title' => $type['title'],
                        'description' => $type['description'],
                        'category_id' => $category->id,
                    ]
                );
                DB::beginTransaction();
                try {
                    NotificationTemplateSection::updateOrCreate(
                        ['notification_type_id' => $emailType->id],
                        [
                            'email_body' => $type['template']['email_body'],
                            'email_subject' => $type['template']['email_subject'],
                            'whats_app_message' => $type['template']['whats_app_message'] ?? null,
                            'sms_message' => $type['template']['sms_message'] ?? null,
                            'bell_notification_message' => $type['template']['bell_notification_message'] ?? null,
                            'app_message' => $type['template']['bell_notification_message'] ?? null,
                            'priority' => $type['template']['priority'],
                            'hidden_pre_header' => $type['template']['hidden_pre_header'],
                            'is_enable' => $type['template']['is_enable'],
                        ]
                    );

                    foreach ($type['variables'] as $variable) {
                        NotificationVariable::updateOrCreate([
                            'notification_type_id' => $emailType->id,
                            'variables' => $variable,
                        ]);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->command->error($emailType->title . ' = ' . $e->getMessage());
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->info("\nEmail templates seeded successfully!");
    }
}
