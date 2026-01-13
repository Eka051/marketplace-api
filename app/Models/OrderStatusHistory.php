<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    protected $primaryKey = 'history_id';

    protected $guarded = [
        'history_id',
        'timestamps'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'changed_by', 'user_id');
    }
}
