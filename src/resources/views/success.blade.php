@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/payment.css') }}">
@endsection

@section('content')
<div class="payment-container success">
    <div class="payment-box">
        <h2 class="payment-title">支払いが完了しました</h2>
        <p class="payment-message">
            ご購入いただきありがとうございます。<br>
            商品の発送準備が整い次第、登録された住所へ発送いたします。
        </p>

        <div class="payment-actions">
            <a href="{{ route('items.index') }}" class="btn btn-primary">トップに戻る</a>
            <a href="{{ route('mypage.index') }}" class="btn btn-outline">購入履歴を見る</a>
        </div>
    </div>
</div>
@endsection