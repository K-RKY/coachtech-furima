@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
<div class="address-container">
    <div class="address-container__inner">
        <h1 class="address-container__title">住所の変更</h1>
        <form class="address-form" action="{{ route('purchase.address.update', ['item_id' => $item_id]) }}" method="POST" novalidate>
            @csrf
            <label class="address-form__label">郵便番号</label>
            <input class="address-form__input" type="text" name="postal_code" value="{{ old('postal_code', $purchaseAddress['postal_code']) }}">
            <span class="error-message">
                @error('postal_code') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="address-form__label">住所</label>
            <input class="address-form__input" type="text" name="address" value="{{ old('address', $purchaseAddress['address']) }}">
            <span class="error-message">
                @error('address') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="address-form__label">建物名</label>
            <input class="address-form__input" type="text" name="building" value="{{ old('building', $purchaseAddress['building']) }}">
            <span class="error-message">
                @error('building') {{ $message }} @else &nbsp; @enderror
            </span>
            <button class="address-button" type="submit">更新する</button>
        </form>
    </div>
</div>
@endsection