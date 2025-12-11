# COACHTECH furima

## 環境構築

### Dockerビルド

1. `git@github.com:K-RKY/coachtech-furima.git`
2. `docker-compose up -d --build`

* MySQLは、OSによって起動方法があるので、それぞれのPCに合わせて `docker-compose.yml` ファイルを編集してください。

### Laravel環境構築

1. `docker-compose exec php bash`
2. `composer install`
3. `.env.exampleファイルから.envを作成し、環境変数を変更
(メール認証用にMAIL_FROM_ADDRESSにno-reply@example.comを設定してください)`
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed`

### Stripe設定

1. `.envにSTRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRETの設定を追加してください`
2. `brew install stripe/stripe-cli/stripe`
3. `stripe login`
4. `stripe listen --forward-to http://127.0.0.1:80/stripe/webhook `
5. `上記コマンドで表示されるwhsec_XXXXXXXXを.envのSTRIPE_WEBHOOK_SECRETに設定してください`

* 商品購入時は`stripe listen --forward-to http://127.0.0.1:80/stripe/webhook `を実行してください

### テスト環境構築

2. `.envから.env.testingを作成し、環境変数をテスト用に変更 (DB_DATABASEはfurima_testにしてください)`
3. `docker-compose exec php bash`
4. `php artisan key:generate --env=testing`
5. `php artisan config:clear`
6. `php artisan migrate --env=testing`

## 使用技術

- PHP 8.0
- Laravel 10.0
- MySQL 8.0

## ER図

<img width="881" height="781" alt="furima" src="https://github.com/user-attachments/assets/9e7e1b3a-a897-41f2-b731-4122a6b1c874" />

## URL

- 開発環境 : [http://localhost](http://localhost)
- phpMyAdmin : [http://localhost:8080](http://localhost:8080)
- mailhog : [http://localhost:8025](http://localhost:8025)


