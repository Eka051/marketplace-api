<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class Address extends Model
{
    protected $primaryKey = 'address_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [
        'address_id',
        'timestamps'
    ];

    public static function booted()
    {
        static::creating(function (Address $address) {
            $address->address_id = (string) Ulid::generate();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    public function district()
    {
        return $this->belongsTo(Disctrict::class, 'district_id', 'district_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id', 'village_id');
    }
}
