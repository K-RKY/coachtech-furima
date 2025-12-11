<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body>
    <p>{{ $purchase->item->name }} の取引が完了しました。</p>
    <p>購入者: {{ $purchase->user->name }}</p>
    <p>取引金額: ¥{{ number_format($purchase->amount) }}</p>
    <p><a href="{{ route('transaction_message.index', ['purchaseId' => $purchase->id]) }}">取引画面へ</a></p>
</body>

</html>