@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="user-info">
    <div class="user-icon-wrap">
        @if($user->image_path)
        <img class="user-icon" src="{{ asset('storage/' . $user->image_path) }}" alt="プロフィール画像">
        @else
        <div class="user-icon user-icon--default"></div>
        @endif
        <div class="user-name-rate">
            <span class="user-name">{{ $user->name }}</span>
            @if($averageRating)
            <div class="review-stars" id="profileStarContainer">
                @for ($i = 1; $i <= 5; $i++)
                    <span class="star {{ $i <= $averageRating ? 'active' : '' }}">★</span>
                    @endfor
            </div>
            @endif
        </div>
    </div>
    <a class="profile-edit-link" href="{{ route('mypage.profile') }}">プロフィールを編集</a>
</div>
<nav class="tabs">
    <a class="tab {{ $tab === 'exhibitions' ? 'active' : '' }}" href="{{ route('mypage.index', ['tab' => 'exhibitions'])}}">出品した商品</a>
    <a class="tab {{ $tab === 'purchasedItems' ? 'active' : '' }}" href="{{ route('mypage.index', ['tab' => 'purchasedItems']) }}">購入した商品</a>
    <a class="tab {{ $tab === 'transactionMessages' ? 'active' : '' }}" href="{{ route('mypage.index', ['tab' => 'transactionMessages']) }}">取引中の商品
        @if($totalUnread)
        <span class="totalUnread">{{ $totalUnread }}</span>
        @endif
    </a>
</nav>
<div class="item-grid">
    @foreach($items as $item)
    <a class="item-link" href="{{ $tab === 'transactionMessages'
        ? route('transaction_message.index', ['purchaseId' => $item->purchase->id])
        : route('items.show', $item->id) }}">
        <div class="item-container">
            <div class="item-image-wrap">
                <img class="item-image {{ $tab === 'exhibitions' && $item->purchase ? 'sold-image' : '' }}"
                    src="{{ Str::startsWith($item->image_path, ['http://','https://'])
                        ? $item->image_path
                        : Storage::url($item->image_path) }}" alt="{{ $item->name }}"
                    alt="{{ $item->name }}">
                @if($tab === 'transactionMessages' && $item->purchase->unread_count)
                <span class="unread-count">{{ $item->purchase->unread_count }}</span>
                @endif
                @if($tab === 'exhibitions' && $item->purchase)
                <div class="sold-overlay">SOLD OUT</div>
                @endif
            </div>
            <span class="item-name">{{ $item->name }}</span>
        </div>
    </a>
    @endforeach
</div>
@endsection