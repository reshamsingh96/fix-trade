<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    use HasFactory, HasUuids;
    protected $table = "notification_types";
    protected $fillable = ['category_id', 'title', 'description', 'type_key'];

    public function notification_template_section()
    {
        return $this->hasOne(NotificationTemplateSection::class, 'notification_type_id', 'id');
    }
    public function notification_variables()
    {
        return $this->hasMany(NotificationVariable::class, 'notification_type_id', 'id');
    }
}
