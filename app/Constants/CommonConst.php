<?php

namespace App\Constants;

class CommonConst
{
  # Account Types
  const SUPER_ADMIN = 'Super Admin';
  const ADMIN = "Admin";
  const USER = "User";
  const SERVICE_PROVIDER = "Service Provider";

  const ACCOUNT = "Account";
  const ACCOUNT_LOGIN = "account_login";
  const PASSWORD_RESET = "password_reset";
  const FORGET_PASSWORD = "forget_password";
  const REGISTER_USER = "register_user";
  const UPDATE_PASSWORD = "update_password";
  const EMAIL_VERIFY_SEND_OTP = 'email_verify_send_otp';

  const MODULE_ALERT_AND_NOTIFICATION = "AlertAndNotification";
  const MODULE_USER = "Users";
  const MODULE_ROLE_PERMISSION = "RolePermission";
  const ROLES = 'Roles';
  const EXPORT_LOG = 'Export Log';
  const NOTIFICATION_LOG = 'Notification Log';

  const PRESENT = 'Present';
  const HALF_PRESENT = 'Half-Present';
  const ABSENT = 'Absent';

  const EMAIL = "Email";
  const SMS = "Sms";
  const BELL_NOTIFICATION = "Bell Notification";
  const WHATSAPP = "WhatsApp";

  const SEND_NOTIFICATION_PLAT_FORM = [self::EMAIL, self::SMS, self::BELL_NOTIFICATION, self::WHATSAPP];

  const SELECT_FILE = "Select File";
  const AUTO_SEND_FILE = "Auto Send File";
  const NO_ATTACHMENT = "No Attachment";
  const SEND_ATTACHMENT_TYPE_LIST = [self::SELECT_FILE, self::AUTO_SEND_FILE, self::NO_ATTACHMENT];

  const CRITICAL = "Critical";
  const LOW = "Low";
  const MEDIUM = "Medium";
  const HIGH = "High";

  const PENDING = "Pending";
  const SUCCESS = "Success";
  const FAILED = "Failed";

  const REQUIRED = "Required";
  const DISABLE = "Disable";
  const ENABLE = "Enable";

  const SEND_VIA = 'Notification';

  const UN_READ = 0;
  const READ = 1;

  const ACTIVE = "Active";
  const IN_ACTIVE = "In-Active";

  # TODO: this list Slug Plz make Unique And Change slug to plz Check Vue or js file in change slug 
  const HEADER_MANAGE_LIST = [
    # Login Log List Header 
    [
      'title' => 'Login Log List',
      'slug' => 'login-log-list',
      'table' => 'user_login_logs',
      'headers' => [
        ['title' => 'Name', 'key' => 'name', 'sortable' => true, 'align' => 'left', 'minWidth' => '140px', 'checked' => true],
        ['title' => 'Ip Address', 'key' => 'ip_address', 'sortable' => true, 'align' => 'left', 'checked' => true],
        ['title' => 'User Agent', 'key' => 'user_agent', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Country', 'key' => 'country', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'State', 'key' => 'state', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'City', 'key' => 'city', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Event', 'key' => 'event', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Date', 'key' => 'logged_at', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'center', 'checked' => true],
      ]
    ],

    # User List Sidebar Menu 
    [
      'title' => 'User List',
      'slug' => 'user-list',
      'table' => 'users',
      'headers' => [
        ['title' => 'Name', 'key' => 'name', 'sortable' => true, 'align' => 'left', 'minWidth' => '140px', 'checked' => true],
        ['title' => 'Email', 'key' => 'email', 'sortable' => true, 'align' => 'left', 'checked' => true],
        ['title' => 'Phone', 'key' => 'phone', 'sortable' => true, 'align' => 'left', 'checked' => true],
        ['title' => 'User Name', 'key' => 'user_name', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Date of Birth', 'key' => 'date_of_birth', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Anniversary Date', 'key' => 'anniversary_date', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Role', 'key' => 'role', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'status', 'key' => 'status', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Actions', 'key' => 'action', 'sortable' => false, 'align' => 'center', 'checked' => true],
      ]
    ],
 
    # Export Logs List Header 
    [
      'title' => 'Export Log List',
      'slug' => 'export-log-list',
      'table' => 'export_logs',
      'headers' => [
        ['title' => 'Name', 'key' => 'name', 'sortable' => true, 'align' => 'left', 'minWidth' => '140px', 'checked' => true],
        ['title' => 'Table Name', 'key' => 'table_name', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'File Path', 'key' => 'file_path', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Created By', 'key' => 'created_by', 'sortable' => false, 'align' => 'left', 'checked' => false],
        ['title' => 'Status', 'key' => 'status', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Extension', 'key' => 'extension', 'sortable' => false, 'align' => 'left', 'checked' => true],
        ['title' => 'Json', 'key' => 'body_params', 'sortable' => false, 'align' => 'left', 'checked' => false],
        ['title' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'center', 'checked' => true],
      ]
    ],
  ];
 
}
