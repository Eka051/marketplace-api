<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    protected $primaryKey = 'option_id';

    protected $guarded = [
        'option_id',
        'timestamps'
    ];

    public function skus()
    {
        return $this->belongsToMany(ProductSku::class, 'sku_variant_options', 'option_id', 'sku_id');
    }
}
