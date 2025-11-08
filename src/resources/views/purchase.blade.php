@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="purchase-container">
    <div class="purchase-left">
        <div class="item-info">
            <div class="item-image">
                @if ($item->image_path)
                <img src="{{ Str::startsWith($item->image_path, ['http://','https://'])
                        ? $item->image_path
                        : Storage::url($item->image_path) }}" alt="{{ $item->name }}">
                @else
                <div class="item-image--default">商品画像</div>
                @endif
            </div>
            <div class="item-detail">
                <span class="item-name">{{ $item->name }}</span>
                <span class="item-price">¥ {{ number_format($item->price) }}</span>
            </div>
        </div>

        <hr>

        {{-- 支払い方法選択 --}}
        <div class="payment-method">
            <p class="payment-method-header">支払い方法</p>
            <select class="payment-method-select" name="payment_method" id="payment_method">
                <option value="none">選択してください</option>
                <option value="konbini">コンビニ支払い</option>
                <option value="card">カード支払い</option>
            </select>
        </div>

        <hr>

        {{-- 配送先住所 --}}
        <div class="shipping-address">
            <div class="shipping-address-container">
                <span class="shipping-address-header">配送先</span>
                <a href="{{ route('purchase.address.edit', ['item_id' => $item->id]) }}" class="change-address">変更する</a>
            </div>
            <p class="shipping-address-data" id=shipping_address>
                〒 {{ $purchaseAddress['postal_code'] ?? '未登録' }}<br>
                {{ $purchaseAddress['address'] ?? '' }}<br>
                {{ $purchaseAddress['building'] ?? '' }}
            </p>
        </div>
    </div>

    {{-- 右側：購入概要 --}}
    <div class="purchase-right">
        <div class="summary-box">
            <div class="summary-row">
                <span>商品代金</span>
                <span class="summary-row-price">¥{{ number_format($item->price) }}</span>
            </div>
            <div class="summary-row">
                <span>支払い方法</span>
                <span class="summary-row-select" id="summary-payment">未選択</span>
            </div>
        </div>

        {{-- 購入ボタン --}}
        <form id="purchaseForm" method="POST" action="{{ route('purchase.checkout', ['item' => $item->id]) }}">
            @csrf
            <input type="hidden" name="payment_method" id="hidden_payment_method">
            <button class="purchase-button" type="submit">購入する</button>
        </form>
    </div>
</div>

<script>
    const paymentSelect = document.getElementById('payment_method');
    const summaryPayment = document.getElementById('summary-payment');
    const hiddenPaymentMethod = document.getElementById('hidden_payment_method');
    const shipping_address = document.getElementById('shipping_address').textContent.trim();

    paymentSelect.addEventListener('change', (e) => {
        const selected = e.target.value;
        if (selected === 'none') label = '未選択';
        if (selected === 'konbini') label = 'コンビニ支払い';
        if (selected === 'card') label = 'カード支払い';
        summaryPayment.textContent = label;
        hiddenPaymentMethod.value = selected;
    });

    document.getElementById('purchaseForm').addEventListener('submit', (e) => {
        let messages = [];

        if (!hiddenPaymentMethod.value) {
            messages.push('支払い方法');
        }
        if (!shipping_address) {
            messages.push('配送先住所');
        }

        if (messages.length > 0) {
            e.preventDefault();
            alert(messages.join('と') + 'を設定してください。');
        }
    });
</script>
@endsection