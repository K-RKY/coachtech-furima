@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/transaction_message.css') }}">
@endsection

@section('content')
<div class="page-container">
    <div class="sidebar">
        <p class="sidebar__title">その他の取引</p>
        <nav class="sidebar__nav">
            @foreach($sidebarItems as $sidebarItem)
            <a class="sidebar__link" href="{{ route('transaction_message.index', ['purchaseId' => $sidebarItem->purchase->id]) }}">{{ $sidebarItem->name }}
                @if($sidebarItem->purchase->unread_count)
                <span class="unread-count">{{ $sidebarItem->purchase->unread_count }}</span>
                @endif
            </a>
            @endforeach
        </nav>
    </div>
    <div class="transaction-container">
        <div class="transaction-header">
            @if($partner->image_path)
            <img class="user-icon" src="{{ asset('storage/' . $partner->image_path) }}" alt="{{ $partner->name }}">
            @else
            <div class="user-icon user-icon--default"></div>
            @endif
            <p class="transaction-header__title">{{ $partner->name }}さんとの取引画面</p>
            <button id="completeTransactionBtn" class="complete-transaction-btn" type="button" {{ $purchase->user_id === $user->id ? '' : 'hidden' }}>取引を完了する</button>
        </div>
        <div class="item-info">
            <img class="item-info__img--default" src="{{ Str::startsWith($item->image_path, ['http://','https://'])
                        ? $item->image_path
                        : Storage::url($item->image_path) }}"
                alt="{{ $item->name }}">
            <div class="item-info__inner">
                <span class="item-name">{{ $item->name }}</span>
                <span class="item-price">{{ $item->price }}</span>
            </div>
        </div>
        <div class="chat-container">
            @foreach($transactionMessages as $msg)
            <div class="{{ $msg->sender_id === $user->id ? 'my-side' : 'their-side' }}">
                <div class="{{ $msg->sender_id === $user->id ? 'my-message' : 'their-message' }}">
                    <div class="user-info">
                        <span class="user-info__name">{{ $msg->sender_id === $user->id ? $user->name :$partner->name }}</span>
                        @if($msg->sender_id === $user->id ? $user->image_path :$partner->image_path)
                        <img class="user-info__icon"
                            src="{{ asset('storage/' . ($msg->sender_id === $user->id ? $user->image_path : $partner->image_path)) }}"
                            alt="{{ $msg->sender_id === $user->id ? $user->name : $partner->name }}">
                        @else
                        <div class="user-info__icon user-info__icon--default"></div>
                        @endif
                    </div>
                    @if($msg->image_path)
                    <div>
                        <img class="message-img" src="{{ asset('storage/' . $msg->image_path) }}" alt="">
                    </div>
                    @endif
                    <div class="message-text"
                        id="msg-text-{{ $msg->id }}"
                        contenteditable="false"
                        data-original="{{ $msg->content }}">
                        {{ $msg->content }}
                    </div>
                    @if($msg->sender_id === $user->id)
                    <div class="message-editor">
                        <button class="message-editor__btn" onclick="enableEdit({{ $msg->id }})">編集</button>
                        <button class="message-editor__btn" onclick="saveEdit({{ $msg->id }})" style="display:none;">保存</button>
                        <button class="message-editor__btn">削除</button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="img-preview"></div>
        @if($errors->has('content') || $errors->has('image'))
        <span class="error-message">{{ $errors->first('content') ?: $errors->first('image') }}</span>
        @endif

        <form class="input-container" id="message-form" enctype="multipart/form-data">
            @csrf
            <textarea class="input-textarea" name="content" placeholder="取引メッセージを記入してください" rows="1"></textarea>
            <button class="input-img-btn" type="button">画像を追加</button>
            <input class="input-img" type="file" name="image" hidden>
            <button class="input-send-btn" type="submit">
                <svg width="45" height="45" viewBox="0 0 24 24" fill="none"
                    stroke="#ccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 2L11 13"></path>
                    <path d="M22 2L15 22L11 13L2 9L22 2"></path>
                </svg>
            </button>
        </form>
    </div>
</div>

<input type="hidden" id="pendingReviewInput" value="{{ $pendingReview }}">

<form id="reviewForm" action="{{ route('reviews.store') }}" method="POST">
    @csrf
    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
    <input type="hidden" id="ratingValue" name="rating" value="0">

    <!-- モーダルウィンドウ -->
    <div id="completeTransactionModal" class="modal">
        <div class="modal-content">

            <div class="modal-header">
                <p class="modal-title">取引が完了しました。</p>
            </div>

            <div class="modal-body">
                <p class="modal-text">今回の取引相手はどうでしたか？</p>

                <!-- 星評価 -->
                <div class="review-stars" id="starContainer">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="star" data-value="{{ $i }}">★</span>
                        @endfor
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="confirmCompleteBtn" class="modal-submit-btn">送信する</button>
            </div>

        </div>
    </div>
</form>



<script>
    // 新しいチャットが来ると自動でスクロール
    const chat = document.querySelector('.chat-container');
    chat.scrollTop = chat.scrollHeight;

    document.getElementById('completeTransactionBtn').addEventListener('click', function() {
        document.getElementById('completeTransactionModal').style.display = 'flex';
    });

    // 背景クリックで閉じる
    document.getElementById('completeTransactionModal').addEventListener('click', function(e) {
        // モーダル本体以外（背景）をクリックした場合のみ閉じる
        if (e.target === this) {
            this.style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('#starContainer .star');
        const ratingInput = document.getElementById('ratingValue');

        stars.forEach(star => {
            star.addEventListener('click', () => {
                let value = Number(star.getAttribute('data-value'));
                let current = Number(ratingInput.value);
                let newValue;

                if (value === current) {
                    // ★ 同じ星を押した場合は「1つ減る」
                    newValue = current - 1;
                } else {
                    // ★ 違う星ならその値に更新
                    newValue = value;
                }

                // 値が0未満にならないように
                newValue = Math.max(newValue, 0);

                ratingInput.value = newValue;

                // ★ 星の色を反映
                stars.forEach(s => {
                    s.classList.toggle(
                        'active',
                        Number(s.getAttribute('data-value')) <= newValue
                    );
                });
            });
        });

        document.getElementById('confirmCompleteBtn').addEventListener('click', () => {
            const rating = Number(document.getElementById('ratingValue').value);

            if (rating < 1) {
                alert("星を選択してください。");
                return;
            }

            document.getElementById('reviewForm').submit();
        });

        const pendingReviewInput = document.getElementById('pendingReviewInput');
        const pendingReview = pendingReviewInput.value === "1";

        if (pendingReview) {
            const modal = document.getElementById('completeTransactionModal');
            modal.style.display = 'flex';
        }

        const textarea = document.querySelector('textarea[name="content"]');
        const storageKey = 'transactionMessageContent';

        // ページロード時に保存されている内容を読み込む
        const savedValue = sessionStorage.getItem(storageKey);
        if (savedValue) {
            textarea.value = savedValue;
        }

        // 入力中に `sessionStorage` に保存
        textarea.addEventListener('input', function() {
            sessionStorage.setItem(storageKey, textarea.value);
        });
    });

    // 画像を追加ボタンを押した時の処理
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.querySelector('.input-img');
        const button = document.querySelector('.input-img-btn');
        const previewArea = document.querySelector('.img-preview');

        button.addEventListener('click', () => {
            input.click();
        });

        // ファイル選択時のプレビュー
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                previewArea.innerHTML = `
                <div class="preview-wrapper">
                    <img src="${event.target.result}" class="img-preview-img">
                    <button type="button" class="img-cancel-btn">×</button>
                </div>
            `;
            };
            reader.readAsDataURL(file);
        });

        // 画像取り消し（動的要素なのでイベントデリゲート）
        previewArea.addEventListener('click', function(e) {
            if (e.target.classList.contains('img-cancel-btn')) {
                previewArea.innerHTML = ""; // プレビューを消す
                input.value = ""; // ファイル選択をリセット
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('message-form');
        const chat = document.querySelector('.chat-container');
        const inputImg = document.querySelector('.input-img');
        const previewArea = document.querySelector('.img-preview');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // フォームのデフォルト送信を止める

            const formData = new FormData(form);
            // CSRFトークンを追加
            formData.append('_token', '{{ csrf_token() }}');

            fetch("{{ route('transaction_message.store', ['purchaseId' => $purchase->id]) }}", {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    // 新しいメッセージをチャットに追加
                    const msgHtml = `
            <div class="my-side">
                <div class="my-message">
                    <div class="user-info">
                        <span class="user-info__name">{{ $user->name }}</span>
                        @if($user->image_path)
                        <img class="user-info__icon" src="{{ asset('storage/' . $user->image_path) }}" alt="{{ $user->name }}">
                        @else
                        <div class="user-info__icon user-info__icon--default"></div>
                        @endif
                    </div>
                    ${data.image_path ? `<img class="message-img" src="${data.image_path}" alt="">` : ''}
                    <div class="message-text" id="msg-text-${data.id}" contenteditable="false" data-original="${data.content}">
                        ${data.content}
                    </div>
                    <div class="message-editor">
                        <button class="message-editor__btn" onclick="enableEdit(${data.id})">編集</button>
                        <button class="message-editor__btn" onclick="saveEdit(${data.id})" style="display:none;">保存</button>
                        <button class="message-editor__btn">削除</button>
                    </div>
                </div>
            </div>
            `;
                    chat.insertAdjacentHTML('beforeend', msgHtml);

                    // スクロール
                    chat.scrollTop = chat.scrollHeight;

                    // フォームリセット
                    form.reset();
                    previewArea.innerHTML = '';
                })
                .catch(err => console.error(err));
        });
    });


    function enableEdit(id) {
        const text = document.getElementById(`msg-text-${id}`);
        const editor = text.parentElement.querySelector('.message-editor');
        const editBtn = editor.querySelector('.message-editor__btn:nth-child(1)');
        const saveBtn = editor.querySelector('.message-editor__btn:nth-child(2)');
        const deleteBtn = editor.querySelector('.message-editor__btn:nth-child(3)');

        text.contentEditable = true;
        text.focus();

        // カーソルを末尾へ
        document.execCommand('selectAll', false, null);
        document.getSelection().collapseToEnd();

        // ボタン切り替え
        editBtn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
        deleteBtn.style.display = 'none'; // 編集中は削除を隠す
    }

    function saveEdit(id) {
        const text = document.getElementById(`msg-text-${id}`);
        const editor = text.parentElement.querySelector('.message-editor');
        const editBtn = editor.querySelector('.message-editor__btn:nth-child(1)');
        const saveBtn = editor.querySelector('.message-editor__btn:nth-child(2)');
        const deleteBtn = editor.querySelector('.message-editor__btn:nth-child(3)');

        const newContent = text.innerText;

        text.contentEditable = false;

        // ボタン切り替え
        editBtn.style.display = 'inline-block';
        saveBtn.style.display = 'none';
        deleteBtn.style.display = 'inline-block';

        // Ajax
        fetch(`/transaction_message/update/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    content: newContent
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log("Updated", data);
            });
    }

    // 動的生成される要素なのでイベントデリゲート
    document.querySelector('.chat-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('message-editor__btn') && e.target.innerText === '削除') {
            const msgDiv = e.target.closest('.my-message, .their-message');
            const msgId = msgDiv.querySelector('.message-text').id.split('-')[2];

            if (!confirm('このメッセージを削除しますか？')) return;

            fetch(`/transaction_message/delete/${msgId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        msgDiv.parentElement.remove(); // my-side / their-side を丸ごと削除
                    }
                })
                .catch(err => console.error(err));
        }
    });
</script>
@endsection