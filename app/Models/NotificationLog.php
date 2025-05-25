<?php

namespace App\Models;

use App\Constants\CommonConst;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NotificationLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notification_logs';

    protected $fillable = [
        'priority',
        'status',
        'receiver_contact',
        'subject',
        'content',
        'message',
        'receiver_id',
        'sender_id',
        'is_delete',
        'notification_type_id',
        'module_id',
        'section_type',
        'email_body',
        'additional_info',
        "is_notification",
        "showing_user_ids",
    ];

    public $casts = [
        'email_body' => 'string',
        'additional_info' => 'string',
        'showing_user_ids' => 'array',
    ];
    protected $appends = ['is_read_by_user'];

    # Custom Accessor
    public function getIsReadByUserAttribute()
    {
        $userId = Auth::User()->uuid ?? null;
        if (!$userId)  return false;
        return in_array($userId, $this->showing_user_ids ?? []);
    }

    /**
     * Scope a query to apply a flexible search across key fields.
     */
    public function scopeSearch($query, $search)
    {
        $term = strtolower($search);

        return $query->where(function ($q) use ($term) {
            $q->where('receiver_contact', 'LIKE', "%{$term}%")
                ->orWhere('subject', 'LIKE', "%{$term}%")
                ->orWhere('status', 'LIKE', "%{$term}%")
                ->orWhereHas('notification_type', fn($sub) => $sub->whereRaw("LOWER(title) LIKE ?", ["%{$term}%"]))
                ->orWhereHas('sender', fn($sub) => $sub->whereRaw("LOWER(name) LIKE ?", ["%{$term}%"]));
        });
    }

    public function sender()
    {
        return $this->hasOne(User::class, 'uuid', 'sender_id')->select('uuid', 'name', 'email', 'avatar');
    }

    public function receiver()
    {
        return $this->hasOne(User::class, 'uuid', 'receiver_id')->select('uuid', 'name', 'email', 'avatar');
    }

    public function notification_type()
    {
        return $this->hasOne(NotificationType::class, 'id', 'notification_type_id');
    }
}
