@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/item.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

@endsection

@section('content')
<div class="main-container">
    <div class="left-container">
        <img class="item-image"
            src="{{ Str::startsWith($item->image_path, ['http://','https://'])
                        ? $item->image_path
                        : Storage::url($item->image_path) }}"
            alt="{{ $item->name }}" alt="">
    </div>
    <div class="right-container">
        <h1 class="item-name">{{ $item->name }}</h1>
        <p class="item-brand">{{ $item->brand }}</p>
        <p>¥<span class="item-price">{{ $item->price }}</span> (税込)</p>
        <div class="icon-container">
            <div class="favorite">
                <form action="{{ route('items.favorite', $item->id) }}" method="POST">
                    @csrf
                    <button class="favorite-icon" type="submit" {{ ($isSold || $isOwner) ? 'disabled' : '' }}>
                        @if(auth()->check() && auth()->user()->favoriteItems->contains($item->id))
                        <i class="fa-solid fa-star fa-3x"></i>
                        @else
                        <i class="fa-regular fa-star fa-3x"></i>
                        @endif
                    </button>
                </form>
                <span class="favorite-count">{{ $item->favoritedByUsers->count() }}</span>
            </div>
            <div class="comment">
                <i class="fa-regular fa-comment fa-2x"></i>
                <span class="comment-count">{{ $item->comments_count }}</span>
            </div>
        </div>
        <form class="purchase-form" action="{{ route('purchase.confirm', $item->id) }}" method="GET">
            @csrf
            <button class="submit-button {{ ($isSold || $isOwner) ? 'disabled-button' : '' }}"
                {{ ($isSold || $isOwner) ? 'disabled' : '' }}>
                {{ $isSold ? 'Sold' : '購入手続きへ' }}
            </button>
        </form>

        <h2>商品説明</h2>
        <div class="item-description">{{ $item->description }}</div>

        <h2>商品の情報</h2>
        <div class="item-info">
            <h3 class="item-info__title">カテゴリー</h3>
            <div class="item-info__inner">
                @foreach($item->categories as $category)
                <div class="item-category__wrap">
                    <span class="item-category">{{ $category->name }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="item-info">
            <h3 class="item-info__title">商品の状態</h3>
            <div class="item-condition__wrap">
                <span class="item-condition">{{ $item->condition_text }}</span>
            </div>
        </div>

        <h2 class="comment-header">コメント({{ $item->comments_count }})</h2>
        @foreach($item->comments as $comment)
        <div class="comment-user">
            <div class="user-icon__wrap">
                @if($comment->user->image_path)
                <img class="user-icon" src="{{ asset('storage/' . $comment->user->image_path) }}" alt="ユーザーアイコン">
                @endif
            </div>
            <span class="user-name">{{ $comment->user->name }}</span>
        </div>
        <div class="comment-content">
            {{ $comment->content }}
        </div>
        @endforeach

        <h2 class="item-comment-header">商品へのコメント</h2>
        <form class="comment-form" action="{{ route('comments.store', $item->id) }}" method="POST">
            @csrf
            <textarea class="comment-form__input" name="content" id="" rows="10" {{ $isSold ? 'disabled' : '' }}></textarea>
            <div class="error-area">
                @error('content')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <button class="submit-button {{ $isSold ? 'disabled-button' : '' }}"
                {{ $isSold ? 'disabled' : '' }}>
                コメントを送信する
            </button>
        </form>
    </div>
</div>
@endsection