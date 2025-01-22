<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPackageFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_package_id', 'status'
    ];

    public function user_package(): BelongsTo
    {
        return $this->belongsTo(UserPackage::class, 'user_package_id', 'id');
    }
}
