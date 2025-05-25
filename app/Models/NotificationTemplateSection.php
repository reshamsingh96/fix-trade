<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplateSection extends Model
{
    use HasFactory, HasUuids;

    protected $table = "notification_template_sections";
    protected $fillable = ['notification_type_id', 'is_enable', 'email_subject', 'email_body', "whats_app_message", "sms_message", "app_message", "bell_notification_message", 'hidden_pre_header', 'priority'];
}
