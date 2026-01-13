<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disctrict extends Model
{
    protected $primaryKey = 'district_id';
    protected $guarded = [
        'district_id',
        'timestamps'
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    public function villages()
    {
        return $this->hasMany(Village::class, 'district_id', 'district_id');
    }
}
