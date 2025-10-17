# 業務連絡システム-ローカル開発環境の構築手順

## リポジトリの内容をローカルにクローン

1. GitHubのアカウントが無い場合は[作成](https://docs.github.com/ja/get-started/start-your-journey/creating-an-account-on-github?source=post_page---------------------------)
2. zhd-infoのリポジトリ管理者に自分のアカウントを追加して貰う
3. GitHubにログインして左側のTop repositoriesもしくはサイドバーのRepositoriesから
   zhd-infoを開く
4. ホーム画面右上の自分のアイコンをクリック→メニューから「Settings」をクリック
5. 設定画面で「<>Developer Setting」→「Personal access token」→「Tokens(classic)」をクリック
6. 「Generate new token」→「Generate new token(classic)」をクリック
7. Noteの欄にトークンの名前を記入
   必要であればExpirationにトークン期限を入力
   Select scopesの「repo」にチェックを入れる
   「Generate token」をクリック→トークン生成完了
8. 設定画面からzhd-infoの画面に戻り\[<\>Code\]と書かれた緑色のボタンをクリックして
   Localタブ→HTTPSの下に出ているURLをコピー
9. Ubuntuでbashを開く(Windows+Rキー→「bash」入力→Enter)
10. 「cd ~/」入力→Enter
11. 「git clone」を入力した後半角スペースを入力して
    4.でコピーしたURLを貼り付け(Ctrl+V)→Enter
12. usernameを求められたら自分のアカウント名を入力
13. passwordを求められたらbashをそのままにしてGithubに戻り、
    GitHubホーム画面右上の自分のアイコンをクリック→メニューから「Settings」をクリック
    　「<>Developer Setting」→「Personal access token」→「Tokens(classic)」をクリック
    7.で生成したトークンをコピー→bashに貼り付けしてEnter
    ※赤字の「Regenerate token」ボタンが表示されている場合はそれをクリック
    　→緑の「Regenerate token」でトークンが再生成されるので、これをコピー
14. クローン生成が始まるので、終了まで待機

## .envファイルの編集

1. VSCode(Visual Studio Code)が無い場合は[公式サイト](https://code.visualstudio.com/download)から
   Windows用を選択しダウンロード・インストール
   (必要に応じて[日本語化](https://igawa.co/memos/vs-code%E3%81%AE%E6%97%A5%E6%9C%AC%E8%AA%9E%E5%8C%96%E3%81%A8%E8%A8%80%E8%AA%9E%E8%A8%AD%E5%AE%9A%E3%81%AE%E6%96%B9%E6%B3%95%E3%81%A8%E6%89%8B%E9%A0%86%E3%80%82/))
2. bashで「cd zhd-info」入力→Enter
3. 「code .」を入力→Enter(警告ウィンドウが出た場合は許可をクリック)
4. VSCodeでzhd-infoが開くので、エクスプローラが
   開いていなければ左側最上のエクスプローラアイコンを選択
5. zhd-info-app/.env.localをコピー(Ctrl+C)→その場で貼り付け(Ctrl+V)して
   コピーしたファイルの名称を「.env」に変更

## Dockerコンテナの構築

1. Docker Desktopが無い場合は[公式サイト](https://www.docker.com/ja-jp/get-started/)から
   使用している端末に合ったものを選択し案内に従ってダウンロード・インストール
   (何を選ぶべきか迷った際はコマンドプロンプトでecho %PROCESSOR_ARCHITECTURE%  で出力結果確認)
2. VSCodeが起動していなければ起動、zhd-infoのフォルダを開いておく
3. zhd-infoを開いた状態のVSCodeで
   画面上部の\[ターミナル\]から\[新しいターミナル\]をクリックするか
   Ctrl+Shift+@でターミナルを開く
4. 「docker compose up -d」を入力→Enter	コンテナを構築
5. Docker Desktopを開き、サイドバーの\[Containers\]を選択、
   zhd-infoのステータスが実行中(Running)になっているか確認
   (Nameの左が●になっているか、またはActionsが🔳になっていれば実行中)
6. 実行中になっていなければDocker Desktopからzhd-infoの▷をクリック

## データベースのデータ準備

1. データ挿入用のlaravel.sqlファイルを開発関係者から貰う
2. zhd-info/docker/mysql/init/に任意名称のディレクトリを作成
3. 作成したディレクトリにlaravel.sqlを入れる
4. laravel.sqlを入れたディレクトリ内でbashを開く
5. bashで「mysql -h localhost(DB_HOSTに設定している値を入力) -u zhduser -p laravel < laravel.sql」を入力→Enter
6. パスワードを要求されたら「zhdpass」を入力→Enter
   (入力中の内容は表示されないので注意)
7. sqlスクリプトが完了するまで待機
8. DBeaverが無い場合は[公式サイト](https://dbeaver.io/download/)から
   Windows用を選択し案内に従ってダウンロード・インストール(基本はそのままの設定で進めて問題ないと思います)
9. DBeaverを起動して左側のデータベースナビゲータで
   右クリック→\[作成\]→\[接続\]をクリック
10. 接続タイプはMySQLを選択して次へ
11. Server Host	「localhost」
    ユーザー名	「zhduser」
    パスワード	「zhdpass」
    を入力して\[テスト接続\]→接続済みと出たら問題なし
    \[終了\]をクリック
12. DBeaverでデータベースの各テーブルにデータが入っていることを確認

### bashでmysqlコマンドが使用できない場合

bashで以下のコマンドを実行

1. パッケージリストの更新
   sudo apt update
2. MySQLサーバーのインストール
   sudo apt install mysql-server -y
3. インストール状況の確認
   dpkg -l | grep mysql-server

### DBeaverで「localhost」でアクセスに失敗する場合
localhostの部分を127.0.0.1に変更して再度試してください。

<!-- Windows -->

<!-- 1. [MySQL公式サイト](https://mysql.com)にアクセス
1. \[ダウンロード\]をクリック
2. 「MySQL Community Server」をクリック。
3. 「Select Operating System」が「Microsoft Windows」になっていることを確認したら、
   「Windows (x86, 64-bit), MSI Installer」の横の「Download」をクリック。
4. \[No thanks, just start my download\]をクリック。
5. ダウンロードしたファイルを実行し、インストールを開始。
6. インストールが完了するまで待機
7. タスクバーの検索欄から「環境変数を編集」を検索
8. (ユーザー名)のユーザー環境変数からPathをクリック→「編集」をクリック
9.  「新規」をクリック→C:\Program Files\MySQL\MySQL Server 9.3\binを追加
10. 「OK」をクリック→再起動
11. Docker Desktopを起動して「zhd-info」のコンテナを起動しておく。 -->

## 依存ファイル(vendor)の生成とAPP_KEYの生成

1. VSCodeが起動していなければ起動、zhd-infoのフォルダを開いておく
2. zhd-infoを開いた状態のVSCodeで
   画面上部の\[ターミナル\]から\[新しいターミナル\]をクリックするか
   Ctrl+Shift+@でターミナルを開く
3. ターミナルで以下のコマンドを実行
   vendor生成

   ```
   docker compose exec app bash -lc "cd /var/www/zhd-info-app &&
    composer install --no-interaction --prefer-dist --ignore-platform-reqs"
   ```

   APP_KEYの生成(未設定の場合発行。設定済なら無視されます)
   ```
   docker compose exec app bash -lc "cd /var/www/zhd-info-app &&
   `php artisan key:generate || true`"
   ```

   キャッシュクリア

   ```
   docker compose exec app php artisan config:clear
   docker compose exec app php artisan route:clear
   docker compose exec app php artisan cache:clear
   ```
   
4. ブラウザから
   http://localhost/member/auth
   (ログインID&パスワード:bb5057)と
   http://localhost/admin/auth
   (社員番号&パスワード:admin)から
   それぞれアクセスし、ログインができること、
   メッセージやマニュアルなどが表示されることを確認
   (両方共にベーシック認証のユーザー名zensho、パスワードzensho777)

---

### ブラウザでhttp://localhost～でアクセスした際にlocalhost はデータを送信しませんでした。と出た場合

1. .envファイルを開き、APP_URL=http://localhostを
   APP_URL=http://127.0.0.1 に変更
2. http://127.0.0.1/member/auth と
   http://127.0.0.1/admin/auth から
   再度アクセスを試行してみて下さい。

### ブラウザでログインの際などにlaravel.logのアクセス権限が無いエラーが出た場合

<!-- 1. zhd-info\zhd-info-appのディレクトリでbashを開きsudo chmod 777 -R storage/を入力→Enter -->

1. Docker Desktopでzhd-infoの左の>をクリック
2. zhd-info-appをクリック
3. Execタブに切り替えてchmod 777 -R storage/を入力→Enter
4. 再度アクセスを試みてください

---

## ショートカットコマンド

### Makefileについて

Dockerコンテナ内でマイグレーションの実行などをしないといけないのでMakefileを用意しています。

例：

マイグレーション実行

```
make migrate
```

コンテナ起動

```
make up
```

コンテナ終了

```
make down
```

各コマンドと処理内容についてはリポジトリ直下の

Makefile

を確認してください。

Windowsの場合下記からmakeコマンドをダウンロードできます。

http://gnuwin32.sourceforge.net/packages/make.htm

※更新日が恐ろしく古いですがちゃんと動作します。

※インストール後、環境変数のpathに C:\Program Files (x86)\GnuWin32\bin を通してください。

## vendorフォルダの取扱について

※vendorをマウントすると極端に動作が遅くなるので別ボリュームとしてマウントしています。

### IDE向けのvendorフォルダアップデート

そのため、laravelのフォルダ内でcomposer installする必要があります。

1. ``cd zhd-info-app``
2. ``composer update``

※composer(ver.2系)のインストールが必要です。

### コンテナ内のvendorフォルダアップデート

vendorフォルダをコンテナ内のボリュームにマウントしたのでこのままではローカル環境のコンテナ内のvendorは空になっています。

必ず一度下記コマンドを実行してください。

```
make update
```

## Installation

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

2. .env ファイルを作成

```sh
cp .env.local .env
```

3. 既存のDBを削除 && マイグレート

```sh
php artisan migrate:fresh
```

4. dbの初期データを登録する

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

## Tools

### コード整形する

```sh
./vendor/bin/pint (--test) (-v)
```

--test: 整形せずチェックだけ　-v: 整形内容を表示

### コード解析する

```sh
./vendor/bin/phpstan analyse
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
