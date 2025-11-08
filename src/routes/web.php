<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

//認証ミドルウェア
Route::middleware(['auth', 'verified'])->group(function () {
    //コメント・いいね
    Route::post('/item/{item_id}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/items/{item}/favorite', [FavoriteController::class, 'toggle'])->name('items.favorite');

    //マイページ・プロフィール
    Route::get('/mypage', [UserController::class, 'index'])->name('mypage.index');
    Route::get('/mypage/profile', [UserController::class, 'profile'])->name('mypage.profile');
    Route::patch('/mypage/profile', [UserController::class, 'updateProfile'])->name('mypage.profile.update');

    //購入画面・住所変更
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'confirm'])->name('purchase.confirm');
    Route::post('/purchase/{item}', [PurchaseController::class, 'checkout'])->name('purchase.checkout');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    //商品出品
    Route::get('/sell', [SellController::class, 'create'])->name('sell.create');
    Route::post('/sell', [SellController::class, 'store'])->name('sell.store');
});
//商品一覧・詳細
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('items.show');

//ログイン・ユーザー登録
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// メール認証誘導画面
Route::get('/verify/notice', function () {
    return view('auth.verify-notice');
})->middleware('auth')->name('verification.notice');

// メール認証リンククリック時
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('mypage.profile');
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

// 認証メール再送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

//stripe webhook
Route::post('/stripe/webhook', [PurchaseController::class, 'handleWebhook'])
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)->name('stripe.webhook');

//Thanks画面・キャンセル画面
Route::get('/payment/success', [PurchaseController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PurchaseController::class, 'cancel'])->name('payment.cancel');
