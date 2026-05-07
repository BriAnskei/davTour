<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $fillable = [
        'name',
        'description',
        'location',
        'price',
        'status'
    ];

    public function images()
{
    return $this->hasMany(TourImage::class);
}

public function schedules()
{
    return $this->hasMany(TourSchedule::class, 'tour_id');
}
}