<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProfileRequest;
use App\Models\TransactionMessage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab') ?? 'exhibitions';
        $user = Auth::user();

        $averageRating = $user->averageRating() ?? 0;

        $userId = $user->id;

        // 自分が購入した商品の purchase.id
        $purchasedIds = $user->purchasedItems()
            ->join('purchases as p1', 'items.id', '=', 'p1.item_id')
            ->where('p1.user_id', $userId)
            ->where('p1.status', 'in_progress')
            ->pluck('p1.id');

        // 自分が出品した商品の purchase.id
        $exhibitedIds = $user->exhibitions()
            ->join('purchases as p2', 'items.id', '=', 'p2.item_id')
            ->where('p2.status', 'in_progress')
            ->pluck('p2.id');

        // 重複削除
        $relatedPurchaseIds = $purchasedIds->merge($exhibitedIds)->unique();

        // 未読メッセージの合計
        $totalUnread = TransactionMessage::whereIn('purchase_id', $relatedPurchaseIds)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();

        switch ($tab) {
            case 'exhibitions':
                // 出品した商品を取得
                $items = $user->exhibitions()
                    ->with('purchase')  // 売れたかどうかの状態を取得
                    ->latest()
                    ->get();
                break;
            case 'purchasedItems':
                // 購入した商品を取得
                $items = $user->purchasedItems()
                    ->whereHas('purchase', function ($q) {
                        $q->where('status', 'paid');
                    })
                    ->with('purchase')
                    ->latest()
                    ->get();
                break;
            case 'transactionMessages':
                // 購入した商品（status が in_progress の場合）
                $userId = $user->id;
                $items = $user->purchasedItems()
                    ->whereHas('purchase', function ($q) {
                        $q->where('status', 'in_progress');
                    })
                    ->with(['purchase.transactionMessages' => function ($query) {
                        // メッセージを新着順に並べる
                        $query->latest();
                    }])
                    ->with([
                        'purchase' => fn($q) =>
                        $q->withCount([
                            'transactionMessages as unread_count' => fn($mq) =>
                            $mq->where('sender_id', '!=', $userId)
                                ->where('is_read', false)
                        ])
                    ])
                    ->latest()
                    ->get();

                // 出品した商品（売れた商品、status が in_progress の場合）
                $exhibitedItems = $user->exhibitions() // 出品した商品
                    ->whereHas('purchase', function ($q) {
                        $q->where('status', 'in_progress');
                    })
                    ->with(['purchase.transactionMessages' => function ($query) {
                        // メッセージを新着順に並べる
                        $query->latest();
                    }])
                    ->with([
                        'purchase' => fn($q) =>
                        $q->withCount([
                            'transactionMessages as unread_count' => fn($mq) =>
                            $mq->where('sender_id', '!=', $userId)
                                ->where('is_read', false)
                        ])
                    ])
                    ->latest()
                    ->get();

                // 購入した商品と出品した商品を合併
                $items = $items->merge($exhibitedItems);

                // transactionMessages の最新メッセージの順に items を並べ替える
                $items = $items->sortByDesc(function ($item) {
                    // 最新のメッセージが一番上にくるように並べる
                    $latestMessage = $item->purchase->transactionMessages->where('sender_id', '!=', Auth::user()->id)->first();

                    if ($latestMessage && !$latestMessage->is_read) {
                        return [$latestMessage->updated_at, 1];
                    }

                    return [$item->purchase->created_at, 2];
                });

                break;
        }

        return view('mypage', compact('items', 'user', 'tab', 'totalUnread', 'averageRating'));
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
