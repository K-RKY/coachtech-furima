<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Purchase;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionCompletedMail;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // バリデーション
        $data = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'rating'      => 'required|integer|min:1|max:5',
        ]);

        $purchase = Purchase::with('item')->findOrFail($data['purchase_id']);

        $reviewer_id = auth()->id();

        $partner = $purchase->getPartner(auth()->user());
        $reviewee_id = $partner->id;

        // 二重投稿防止
        if (Review::where('purchase_id', $data['purchase_id'])
            ->where('reviewer_id', $reviewer_id)
            ->exists()
        ) {
            return redirect()->route('items.index')
                ->with('error', 'すでにレビュー済みです。');
        }

        // 保存
        Review::create([
            'purchase_id' => $data['purchase_id'],
            'reviewer_id' => $reviewer_id,
            'reviewee_id' => $reviewee_id,
            'rating'      => $data['rating'],
        ]);

        if ($purchase->user_id != $reviewer_id){
            // 出品者がレビューしたらstatusを更新して、購入した商品に表示させる
            $purchase->update(['status' => 'paid']);
        } else {
            // 購入者がレビューしたら出品者にメールで通知
            Mail::to($purchase->item->seller->email)
                ->send(new TransactionCompletedMail($purchase));
        }

        return redirect()->route('items.index')
            ->with('status', 'レビューを投稿しました');
    }
}
