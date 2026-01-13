<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Symfony\Component\Uid\Ulid;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, SoftDeletes;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public static function booted()
    {
        static::creating(function (User $user) {
            if (empty($user->user_id)) {
                $user->user_id = Ulid::generate();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'email_verified_at',
        'timestamps'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id', 'user_id');
    }

    public function shopReviews()
    {
        return $this->hasMany(ShopReview::class, 'user_id', 'user_id');
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class, 'user_id', 'user_id');
    }

    public function voucherUsages()
    {
        return $this->hasMany(VoucherUsage::class, 'user_id', 'user_id');
    }

    public function orderStatusActions()
    {
        return $this->hasMany(OrderStatusHistory::class, 'changed_by', 'user_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'user_id');
    }
}
