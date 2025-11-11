<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capsule extends Model
{
    use HasFactory;

    protected $fillable = [
        'capsule_serial', 
        'capsule_id', 
        'status', 
        'original_launch', 
        'missions_count', 
        'details', 
        'raw_data'
    ];
}