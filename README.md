# zhd-info

mainブランチからreleaseブランチ作成
```
git subtree push --prefix zhd-info-app origin release
```

サーバー側で以下コマンドでプルする
```
git pull origin release -f
```

シーダーを作成
```
php artisan db:seed
```

DBを削除 && マイグレート
```
php artisan migrate:fresh
```
