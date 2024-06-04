<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    use HasFactory;

    protected $fillable = [
        'current_x',
        'current_y',
        'target_x',
        'target_y',
        'distance',
        'geometry',
    ];
}
