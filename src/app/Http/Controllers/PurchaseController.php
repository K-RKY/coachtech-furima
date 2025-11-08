<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Models\Purchase;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function confirm($item_id)
    {
        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        $purchaseAddress = session('purchase_address', [
            'postal_code' => $user->postal_code ?? '',
            'address'     => $user->address ?? '',
            'building'    => $user->building ?? '',
        ]);

        return view('purchase', compact('item', 'user', 'purchaseAddress'));
    }

    /**
     * Checkout セッション作成・Stripeへ遷移
     */
    public function checkout(Request $request, Item $item)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'payment_method' => 'required|in:card,konbini',
        ]);

        $payment_method = $validated['payment_method'];

        // 配送先住所
        $shipping = session('purchase_address', [
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building,
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = StripeSession::create([
            'payment_method_types' => [$payment_method],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success'),
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'user_id' => $user->id,
                'item_id' => $item->id,
                'payment_method' => $payment_method,
                'postal_code' => $shipping['postal_code'],
                'address' => $shipping['address'],
                'building' => $shipping['building'],
            ],
        ]);

        \Stripe\Checkout\Session::update(
            $session->id,
            ['metadata' => array_merge($session->metadata->toArray(), ['session_id' => $session->id])]
        );

        return redirect($session->url);
    }

    /**
     * Stripe Webhook受信
     */
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response('', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;
        }

        return response('Webhook handled', 200);
    }

    /**
     * カード・コンビニ共通: Checkout完了時
     */
    protected function handleCheckoutCompleted($session)
    {
        try {
            $user_id = intval($session->metadata->user_id ?? 0);
            $item_id = intval($session->metadata->item_id ?? 0);

            $postal = $session->metadata->postal_code ?? '';
            $address = $session->metadata->address ?? '';
            $building = $session->metadata->building ?? '';
            $shipping_address = trim("$postal $address $building");

            $status = $session->payment_status === 'paid' ? 'paid' : 'pending';
            $amount = intval($session->amount_total ?? 0);

            // 重複チェック
            if (Purchase::where('stripe_session_id', $session->id)->exists()) {
                return;
            }

            Purchase::create([
                'user_id' => $user_id,
                'item_id' => $item_id,
                'payment_method' => $session->metadata->payment_method ?? 'unknown',
                'shipping_address' => $shipping_address,
                'status' => $status,
                'amount' => $amount,
                'stripe_session_id' => $session->id,
            ]);

            Log::info('CheckoutCompleted session', [
                'session_id' => $session->id,
                'metadata' => (array)$session->metadata,
                'amount_total' => $session->amount_total ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Webhook Purchase create error: ' . $e->getMessage(), [
                'metadata' => (array)$session->metadata,
                'amount_total' => $session->amount_total ?? null,
            ]);
        }
    }


    /**
     * コンビニ払い入金完了時
     */
    protected function handlePaymentSucceeded($paymentIntent)
    {
        $session_id = $paymentIntent->metadata->session_id ?? null;

        if ($session_id) {
            $purchase = Purchase::where('stripe_session_id', $session_id)->first();
            if ($purchase) {
                $purchase->update(['status' => 'paid']);
            }
        }
    }

    /**
     * 支払い成功画面
     */
    public function success()
    {
        return view('success');
    }

    /**
     * 支払いキャンセル画面
     */
    public function cancel()
    {
        return view('cancel');
    }

    public function editAddress($item_id)
    {
        $user = Auth::user();

        $purchaseAddress = session('purchase_address', [
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building,
        ]);

        return view('address', compact('purchaseAddress', 'item_id'));
    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $validated = $request->validated();

        session(['purchase_address' => $validated]);

        return redirect()->route('purchase.confirm', ['item_id' => $item_id])->with('status', '配送先住所を変更しました');
    }
}
