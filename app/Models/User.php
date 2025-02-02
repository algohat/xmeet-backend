<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'set_password',
        'age',
        'phone',
        'gender',
        'design_id',
        'interest',
        'post_code',
        'is_verified',
        'otp_verified_at',
        'last_login_at',
        'is_disable',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'design_id' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getLastLoginAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function userPackage()
    {
        return $this->hasOne(UserPackage::class)->where('status', 2)->with(['userPackageFeature']);
    }

    public function chatOpens()
    {
        return $this->hasMany(ChatOpen::class, 'sender_id')
            ->orWhere('receiver_id', $this->id);
    }

    public function chatOpenedUsers()
    {
        return $this->belongsToMany(User::class, 'chat_opens', 'sender_id', 'receiver_id')
            ->orWhere('chat_opens.receiver_id', $this->id);
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'reporter_id', 'user_id')
            ->withTimestamps();
    }

    public function blockedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'user_id', 'reporter_id')
            ->withTimestamps();
    }
}
