<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $table = 'users';

    const SUPER_ADMIN = 'Super Admin';
    const USER = 'User';
    const LABOR = 'Labor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'gender',
        'name',
        'email',
        'phone_code',
        'phone',
        'account_type',
        'status',
        'date_of_birth',
        'anniversary_date',
        'image_url',
        'search_key',
        'password',
        'ip_address',
        'user_name',
        'phone_verified_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'search_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Boot the model and set UUID for the id attribute.
     *
     * @return void
     */
    // Boot method to generate UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            do {
                $uuid = (string) Str::uuid();
            } while (User::where('uuid', $uuid)->exists());

            $user->uuid = $uuid;

            if (empty($user->ip_address)) {
                $user->ip_address = request()->ip() ?? '127.0.0.1';
            }
        });
    }

    public function user_address()
    {
        return $this->hasMany(UserAddress::class, 'user_id', 'uuid')->with(['country:id,name', 'state:id,name', 'city:id,name']);
    }

    public function store()
    {
        return $this->hasMany(Store::class, 'user_id', 'uuid');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id', 'uuid', 'id');
    }

    public function hasRole($slug)
    {
        return $this->roles->contains('slug', $slug);
    }

    public function user_role()
    {
        return $this->hasMany(UserRole::class, 'user_id', 'uuid');
    }

    public function getPermissionsViaRoles()
    {
        return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten()->unique('id');
    }
}
