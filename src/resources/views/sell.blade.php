@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section('content')
<div class="sell-container">
    <div class="sell-container__inner">
        <h1 class="sell-container__title">商品の出品</h1>
        <form class="sell-form" action="{{ route('sell.store') }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            <label class="sell-form__label">商品画像</label>
            <div class="sell-form-image">
                <img class="item-image" src="" alt="">
                <input class="select-image" type="file" accept="image/*" name="image">
                <button class="custom-file-button">画像を選択する</button>
            </div>
            <span class="error-message">
                @error('image') {{ $message }} @else &nbsp; @enderror
            </span>
            <div class="sell-info">
                <h2>商品の詳細</h2>
                <hr>
            </div>
            <label class="sell-form__label">カテゴリー</label>
            <div class="category-list">
                @foreach($categories as $category)
                <button class="category-button" type="button" data-id="{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>
            <input type="hidden" name="categories" id="selected-categories" value="">
            <span class="error-message">
                @error('category_id') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="sell-form__label">商品の状態</label>
            <select class="sell-form__input" name="condition_id" id="">
                <option value="">選択してください</option>
                <option value="1">良好</option>
                <option value="2">目立った傷や汚れなし</option>
                <option value="3">やや傷や汚れあり</option>
                <option value="4">状態が悪い</option>
            </select>
            <span class="error-message">
                @error('condition_id') {{ $message }} @else &nbsp; @enderror
            </span>
            <div class="sell-info">
                <h2>商品名と説明</h2>
                <hr>
            </div>
            <label class="sell-form__label">商品名</label>
            <input class="sell-form__input" type="text" name="name" value="{{ old('name') }}">
            <span class="error-message">
                @error('name') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="sell-form__label">ブランド名</label>
            <input class="sell-form__input" type="text" name="brand">
            <span class="error-message">
                @error('brand') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="sell-form__label">商品の説明</label>
            <textarea class="sell-form__textarea" name="description" rows="10"></textarea>
            <span class="error-message">
                @error('description') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="sell-form__label">商品価格</label>
            <input class="sell-form__input" type="text" name="price" placeholder="¥">
            <span class="error-message">
                @error('price') {{ $message }} @else &nbsp; @enderror
            </span>
            <button class="sell-button" type="submit">出品する</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.category-button');
        const hiddenInput = document.getElementById('selected-categories');

        buttons.forEach(button => {
            button.addEventListener('click', function() {
                this.classList.toggle('selected');

                const selectedIds = Array.from(document.querySelectorAll('.category-button.selected'))
                    .map(btn => btn.dataset.id);

                hiddenInput.value = selectedIds.join(',');
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const sellImageWrap = document.querySelector('.sell-form-image');
        const fileInput = document.querySelector('.select-image');
        const customButton = document.querySelector('.custom-file-button');
        const previewImage = document.querySelector('.item-image');

        // ボタンをクリックしたら input[type=file] を開く
        customButton.addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.click();
        });

        // ファイル選択後にプレビュー表示
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
                sellImageWrap.style.justifyContent = 'space-around';
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endsection