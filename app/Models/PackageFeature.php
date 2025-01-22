<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'feature_type',
        'title',
        'value',
        'time_limit',
        'time_option',
        'description',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
