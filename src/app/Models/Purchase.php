<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'payment_method',
        'shipping_address',
        'status',
        'amount',
        'stripe_session_id',
    ];

    // 購入はユーザーに属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 購入は商品に属する
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
