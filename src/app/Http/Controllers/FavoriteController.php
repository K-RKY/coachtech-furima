<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Item $item)
    {
        $user = Auth::user();

        if ($user->favoriteItems()->where('item_id', $item->id)->exists()) {
            // 既にいいね済み → 解除
            $user->favoriteItems()->detach($item->id);
            $status = 'removed';
        } else {
            // いいね追加
            $user->favoriteItems()->attach($item->id);
        }

        return back();
    }
}
