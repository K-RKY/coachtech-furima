<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TransactionMessageRequest;
use App\Models\TransactionMessage;
use App\Models\Purchase;

class TransactionMessageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $pendingReview = false; // レビュー待ちかどうかのフラグ

        if ($request->has('purchaseId')) {
            // マイページからの呼び出し
            $purchaseId = $request->query('purchaseId');
            $purchase = Purchase::with('item', 'transactionMessages', 'reviews')->findOrFail($purchaseId);
        } elseif ($request->has('session_id')) {
            // Stripe Checkout 成功後の呼び出し
            $sessionId = $request->query('session_id');
            $purchase = Purchase::with('item', 'transactionMessages')
                ->where('stripe_session_id', $sessionId)
                ->firstOrFail();
        } else {
            abort(404, '取引データが見つかりません。');
        }

        // 該当取引データが取引中かを検証
        if ($purchase->status != 'in_progress'){
            abort(403, 'この取引は既に完了しています。');
        }

        // ユーザーが出品者であるかを検証し、レビュー待ちかどうかを判定
        $isSeller = $purchase->seller()->id == $user->id;
        if ($isSeller){
            $pendingReview = $purchase->reviews()->exists();
        }

        $item = $purchase->item;
        $partner = $purchase->getPartner($user);

        // 未読のメッセージを取得して既読にする
        TransactionMessage::where('purchase_id', $purchase->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $transactionMessages = $purchase->transactionMessages;

        $sidebarItems = $this->getSidebarItems($user, $purchase->id);

        return view('transaction_message', compact('user', 'purchase', 'item', 'partner', 'transactionMessages', 'sidebarItems', 'pendingReview'));
    }

    public function getSidebarItems($user, $purchaseId)
    {
        $items = $user->purchasedItems()
            ->whereHas('purchase', function ($q) use ($purchaseId) {
                $q->where('status', 'in_progress')
                    ->where('id', '!=', $purchaseId);
            })
            ->with(['purchase.transactionMessages' => function ($query) {
                // メッセージを新着順に並べる
                $query->latest();
            }])
            ->with([
                'purchase' => fn($q) =>
                $q->withCount([
                    'transactionMessages as unread_count' => fn($mq) =>
                    $mq->where('sender_id', '!=', $user->id)
                        ->where('is_read', false)
                ])
            ])
            ->latest()
            ->get();

        // 出品した商品（売れた商品、status が in_progress の場合）
        $exhibitedItems = $user->exhibitions() // 出品した商品
            ->whereHas('purchase', function ($q) use ($purchaseId) {
                $q->where('status', 'in_progress')
                    ->where('id', '!=', $purchaseId);
            })
            ->with(['purchase.transactionMessages' => function ($query) {
                // メッセージを新着順に並べる
                $query->latest();
            }])
            ->with([
                'purchase' => fn($q) =>
                $q->withCount([
                    'transactionMessages as unread_count' => fn($mq) =>
                    $mq->where('sender_id', '!=', $user->id)
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

        return $items;
    }

    public function store(TransactionMessageRequest $request)
    {
        $user = Auth::user();
        $purchaseId = $request->query('purchaseId');

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('transaction_images', 'public');
        }

        TransactionMessage::create([
            'purchase_id' => $purchaseId,
            'sender_id' => $user->id,
            'content' => $request->content,
            'image_path' => $path,
        ]);

        return redirect()->route('transaction_message.index', ['purchaseId' => $purchaseId]);
    }


    public function update(TransactionMessageRequest $request, $id)
    {
        $msg = TransactionMessage::findOrFail($id);

        if ($msg->sender_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => '権限がありません'], 403);
        }

        $msg->update([
            'content' => $request->content,
            'is_read' => false,
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function destroy($id)
    {
        $msg = TransactionMessage::findOrFail($id);

        if ($msg->sender_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => '権限がありません'], 403);
        }

        $msg->delete();

        return response()->json(['status' => 'ok']);
    }
}
