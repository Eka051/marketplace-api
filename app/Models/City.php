<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $primaryKey = 'city_id';

    protected $guarded = [
        'city_id',
        'timestamps'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function districts()
    {
        return $this->hasMany(Disctrict::class, 'city_id', 'city_id');
    }
}
