<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'postal_code',
        'address',
        'building',
        'image_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favoriteItems()
    {
        return $this->belongsToMany(Item::class, 'favorites')->withTimestamps();
    }

    //購入した商品を取得
    public function purchasedItems()
    {
        return $this->hasManyThrough(
            Item::class,
            Purchase::class,
            'user_id', // purchases.user_id
            'id',      // items.id
            'id',      // users.id
            'item_id'  // purchases.item_id
        );
    }

    //出品した商品を取得
    public function exhibitions()
    {
        return $this->hasMany(Item::class, 'user_id');
    }
}
