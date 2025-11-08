@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/payment.css') }}">
@endsection

@section('content')
<div class="payment-container cancel">
    <div class="payment-box">
        <h2 class="payment-title">支払いがキャンセルされました</h2>
        <p class="payment-message">
            支払いをキャンセルしました。<br>
            再度購入を行う場合は、もう一度商品ページからお手続きください。
        </p>

        <div class="payment-actions">
            <a href="{{ route('items.index') }}" class="btn btn-primary">トップに戻る</a>
        </div>
    </div>
</div>
@endsection