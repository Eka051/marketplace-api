<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $primaryKey = 'village_id';

    protected $guarded = [
        'village_id',
        'timestamp'
    ];

    public function district()
    {
        return $this->belongsTo(Disctrict::class, 'district_id', 'district_id');
    }
}
