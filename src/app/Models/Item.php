<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public const CONDITIONS = [
        1 => '良好',
        2 => '目立った傷や汚れなし',
        3 => 'やや傷や汚れあり',
        4 => '傷や汚れあり',
    ];

    public function getConditionTextAttribute()
    {
        return self::CONDITIONS[$this->condition_id] ?? '未設定';
    }

    protected $fillable =[
        'user_id',
        'name',
        'brand',
        'description',
        'condition_id',
        'price',
        'image_path',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    //購入したユーザーを取得
    public function buyer()
    {
        return $this->hasOneThrough(
            User::class,
            Purchase::class,
            'item_id',
            'id',
            'id',
            'user_id',
        );
    }

    //販売したユーザーを取得
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
