@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<nav class="tabs">
    <a class="tab {{ request('tab') !== 'mylist' ? 'active' : '' }}" href="{{ route('items.index', ['keyword' => request('keyword')]) }}">おすすめ</a>
    <a class="tab {{ request('tab') === 'mylist' ? 'active' : '' }}" href="{{ route('items.index', ['tab' => 'mylist', 'keyword' => request('keyword')]) }}">マイリスト</a>
</nav>
<div class="item-grid">
    @foreach($items as $item)
    <a class="item-link" href="{{ route('items.show', $item->id) }}">
        <div class="item-container">
            <div class="item-image-wrap">
                <img class="item-image {{ $item->purchase ? 'sold-image' : '' }}"
                    src="{{ Str::startsWith($item->image_path, ['http://','https://'])
                        ? $item->image_path
                        : Storage::url($item->image_path) }}" alt="{{ $item->name }}"
                    alt="{{ $item->name }}">
                @if($item->purchase)
                <div class="sold-overlay">Sold</div>
                @endif
            </div>
            <span class="item-name">{{ $item->name }}</span>
        </div>
    </a>
    @endforeach
</div>
@endsection