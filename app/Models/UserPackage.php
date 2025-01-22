<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'package_name', 'package_type', 'start_date',
        'end_date', 'payment_medium', 'transaction_id',
        'user_original_id', 'user_name', 'user_email', 'user_phone'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_original_id');
    }

    public function package(): HasOne
    {
        return $this->hasOne(Package::class, 'id', 'package_id');
    }

    public function userPackageFeature(): HasMany
    {
        return $this->hasMany(UserPackageFeature::class, 'user_package_id', 'id');
    }
}
