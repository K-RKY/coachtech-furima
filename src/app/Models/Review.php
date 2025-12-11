<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'reviewer_id',
        'reviewee_id',
        'rating',    // 1〜5の星
        'comment',   // optional
    ];

    // このレビューに紐づく購入
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    // レビューしたユーザー
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // レビューされたユーザー
    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }
}
