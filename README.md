# zhd-info

## Information

Flamework: Laravel  
Launguage: PHP  
DB: MySQL  

## Instrallation

### ホストでの作業

1. envファイルを作成する

2. コンテナを起動する

```sh
docker-compose up
```

### Appコンテナでの作業

1. パッケージをinstallする

```sh
composer install --ignore-platform-reqs
```

2. 既存のDBを削除 && マイグレート

```sh
php artisan migrate:fresh
```

3. dbの初期データを登録する

```sh
php artisan db:seed
```

## Deploy

### release用のブランチを作成する

mainブランチからreleaseブランチ作成

```sh
git subtree push --prefix zhd-info-app origin release
```

### サーバー側で以下コマンドでプルする

```sh
git fetch origin release
git reset --hard origin/release
```

## Help

### configのキャッシュをクリアする

```sh
php artisan config:clear
```

### laravel-debugbarをオフにする

- .envのDEBUGBAR_ENABLEDをfalseに書き換える

```php
DEBUGBAR_ENABLED=false
```

- zhd-info-app/app/config/querydetector.php のoutputの中をコメントアウトする

```php
'output' => [
    // \BeyondCode\QueryDetector\Outputs\Debugbar::class,
    // \BeyondCode\QueryDetector\Outputs\Alert::class,
    // \BeyondCode\QueryDetector\Outputs\Log::class,
]
```
