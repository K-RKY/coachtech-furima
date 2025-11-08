@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="profile-container">
    <div class="profile-container__inner">
        <h1 class="profile-container__title">プロフィール設定</h1>
        <form class="profile-form" action="{{ route('mypage.profile.update') }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="user-info">
                <div class="user-icon-wrap">
                    @if($user->image_path)
                    <img class="user-icon" src="{{ asset('storage/' . $user->image_path) }}" alt="プロフィール画像">
                    @else
                    <div class="user-icon user-icon--default"></div>
                    @endif
                </div>
                <input type="file" class="select-image" name="image" accept="image/*">
                <button type="button" class="custom-file-button">画像を選択する</button>
            </div>
            <label class="profile-form__label">ユーザー名</label>
            <input class="profile-form__input" type="text" name="name" value="{{ old('name', $user->name) }}">
            <span class="error-message">
                @error('name') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="profile-form__label">郵便番号</label>
            <input class="profile-form__input" type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}">
            <span class="error-message">
                @error('postal_code') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="profile-form__label">住所</label>
            <input class="profile-form__input" type="text" name="address" value="{{ old('address', $user->address) }}">
            <span class="error-message">
                @error('address') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="profile-form__label">建物名</label>
            <input class="profile-form__input" type="text" name="building" value="{{ old('building', $user->building) }}">
            <span class="error-message"></span>
            <button class="update-button" type="submit">更新する</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.querySelector('.select-image');
        const button = document.querySelector('.custom-file-button');
        const wrapper = document.querySelector('.user-icon-wrap');

        let preview = wrapper.querySelector('.user-icon') || wrapper.querySelector('.user-icon--default');

        button.addEventListener('click', () => {
            input.click();
        });

        input.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    if (!preview || preview.classList.contains('user-icon--default')) {
                        preview?.remove();
                        preview = document.createElement('img');
                        preview.classList.add('user-icon');
                        wrapper.prepend(preview);
                    }
                    preview.src = event.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                preview?.remove();
                if ("{{ $user->image_path }}") {
                    preview = document.createElement('img');
                    preview.src = "{{ asset('storage/' . $user->image_path) }}";
                    preview.classList.add('user-icon');
                } else {
                    preview = document.createElement('div');
                    preview.classList.add('user-icon', 'user-icon--default');
                }
                wrapper.prepend(preview);
            }
        });
    });
</script>
@endsection