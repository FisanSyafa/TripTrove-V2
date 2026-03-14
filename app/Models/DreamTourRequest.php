<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DreamTourRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country_code',
        'departure_date',
        'num_adults',
        'num_children',
        'destinations',
        'additional_info',
        'status',
    ];

    protected $casts = [
        'destinations' => 'array',
        'departure_date' => 'date',
    ];
}
