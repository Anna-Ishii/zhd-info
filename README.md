# zhd-info

## Information

Flamework: Laravel  
Launguage: PHP  
DB: MySQL  

## Instrallation

### ホストでの作業

envファイルを作成する

コンテナを起動する

```sh
docker-compose up
```

### Appコンテナでの作業

パッケージをinstallする

```sh
composer install
```

既存のDBを削除 && マイグレート

```sh
php artisan migrate:fresh
```

dbの初期データを登録する

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
git pull origin release
```
