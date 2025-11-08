<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProfileRequest;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab');
        $user = Auth::user();

        // 未ログインユーザー対応
        if (!$user) {
            return redirect()->route('login')->with('error', 'ログインしてください。');
        }

        if ($tab === 'exhibitions') {
            // 出品した商品を取得
            $items = $user->exhibitions()
                ->with('purchase')  // 売れたかどうかの状態を取得
                ->latest()
                ->get();
        } else {
            // 購入した商品を取得
            $items = $user->purchasedItems()
                ->with('purchase')
                ->latest()
                ->get();
        }

        return view('mypage', compact('items', 'user'));
    }


    public function profile()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function updateProfile(ProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
                Storage::disk('public')->delete($user->image_path);
            }

            $path = $request->file('image')->store('profile_images', 'public');

            $data['image_path'] = $path;
        }

        $user->update($data);

        return redirect()->route('mypage.profile')->with('status', 'プロフィールが更新されました');
    }
}
