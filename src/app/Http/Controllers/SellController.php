<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Support\Facades\Auth;

class SellController extends Controller
{
    public function create()
    {
        $categories = Category::all();

        return view('sell', compact('categories'));
    }

    // 商品登録
    public function store(ExhibitionRequest $request)
    {
        $data = $request->validated();

        // 画像アップロード
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('item_images', 'public');
            $data['image_path'] = $path;
        }

        // 商品作成
        $product = Item::create([
            'user_id'     => Auth::id(),
            'name'        => $data['name'],
            'price'       => $data['price'],
            'brand'       => $data['brand'] ?? null,
            'description' => $data['description'] ?? null,
            'condition_id' => $data['condition_id'],
            'image_path'  => $data['image_path'] ?? null,
        ]);

        // カテゴリ紐付け（多対多）
        if (!empty($data['categories'])) {
            $categoryIds = explode(',', $data['categories']);
            $product->categories()->sync($categoryIds);
        }

        return redirect()->route('items.index')->with('status', '商品を出品しました');
    }
}