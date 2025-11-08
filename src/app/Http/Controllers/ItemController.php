<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab');
        $keyword = $request->query('keyword');

        if ($tab === 'mylist') {
            // マイリストタブ
            if (Auth::check()) {
                $user = Auth::user();
                $items = $user->favoriteItems()
                    ->with('purchase')
                    ->where('items.user_id', '!=', $user->id)
                    ->when($keyword, function ($query, $keyword) {
                        $query->where('items.name', 'like', "%{$keyword}%")
                            ->orWhere('items.description', 'like', "%{$keyword}%");
                    })
                    ->get();
            } else {
                $items = collect();
            }
        } else {
            // おすすめ
            $query = Item::with('purchase')->latest();

            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }

            $query->when($keyword, function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });

            $items = $query->get();
        }

        return view('index', compact('items', 'keyword'));
    }


    public function show($item_id)
    {
        $item = Item::with([
            'categories',
            'comments.user',
            'favoritedByUsers',
            'purchase',
        ])
            ->withCount('comments')
            ->findOrFail($item_id);

        $isSold = $item->purchase !== null;
        $isOwner = $item->user_id === auth()->id();

        return view('item', compact('item', 'isSold', 'isOwner'));
    }
}
