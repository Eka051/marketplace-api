<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class Voucher extends Model
{
    protected $primaryKey = 'voucher_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [
        'voucher_id',
        'timestamps'
    ];

    public static function booted()
    {
        static::class(function (Voucher $voucher) {
            $voucher->voucher_id = (string) Ulid::generate();
        });
    }

    public function voucherUsages()
    {
        return $this->hasMany(VoucherUsage::class, 'voucher_id', 'voucher_id');
    }
}
