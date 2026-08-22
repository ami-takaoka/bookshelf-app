# BookShelf
書籍レビューアプリ「BookShelf」

## 概要
BookShelfは、Laravelを使用して開発した書籍レビューアプリです。
書籍の登録・検索・レビュー・お気に入り管理などの基本機能に加え、読書計画、通知、読書レポート、ランキングなどの応用機能を実装しています。

また、公開API、Laravel SanctumによるAPIトークン認証、Google Books API連携、スケジュールによるバッチ処理などを通して、Laravelを用いたWebアプリケーション開発の実践的な機能を実装しています。

## 主な機能

### 基本機能

- ユーザー登録・ログイン
- 書籍一覧・詳細表示
- 書籍登録・編集・削除
- ジャンル登録・編集・削除
- お気に入り登録・解除
- レビュー投稿・編集・削除
- レビューへのいいね
- ランキング
- 公開API

### 応用機能

- 書籍のキーワード検索
- ジャンルによる絞り込み
- 書籍の並び順変更
- ISBNによる書籍情報検索（Google Books API）
- 読書計画
- 読書レポート
- 通知
- 読書計画のリマインダー
- 読書計画の自動失効
- Laravel SanctumによるAPIトークン認証


## 環境構築

### Dockerビルド

1. GitHubからリポジトリをクローン
```bash
git clone git@github.com:ami-takaoka/bookshelf-app.git
```

2. Docker Desktopを起動する

3. プロジェクトディレクトリへ移動
```bash
cd bookshelf-app
```

4. `.env`ファイル作成
`.env.example`をコピーして`.env`を作成してください。
```bash
cp .env.example .env
```

以下のデータベース接続情報を設定してください。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

5. Composerの依存パッケージをインストール
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer install
```

6. Laravel SailのDockerコンテナを起動
```bash
sail up -d --build
```
Sailコマンドが利用できない場合：
```bash
./vendor/bin/sail up -d --build
```

### Laravel環境構築

1. アプリケーションキーを生成
```bash
sail artisan key:generate
```

2. データベースを構築し、初期データを投入
```bash
sail artisan migrate --seed
```
データベースをリセットする場合：
```bash
sail artisan migrate:fresh --seed
```

3. フロントエンドの依存関係をインストール
```bash
sail npm install
```

4. Vite開発サーバーを起動
```bash
sail npm run dev
```

5. Google Books APIの設定

ISBN検索機能を使用するため、Google Books APIのAPIキーを取得し、`.env`に設定してください。

```env
GOOGLE_BOOKS_API_KEY=your_api_key
```

6. Laravelスケジューラーの設定
読書計画のリマインダー通知および自動失効処理を実行するため、Laravelのスケジューラーを使用しています。

スケジューラーの登録内容を確認する場合：
```bash
sail artisan schedule:list
```

スケジューラーを起動する場合：
```bash
sail artisan schedule:work
```

7. アプリ確認
```
http://localhost
```

8. phpMyAdminを起動・利用
```
http://localhost:8080
```

## ダミーデータ

Seeder実行により以下のデータが登録されます。

- ユーザー情報
- 書籍情報
- ジャンル情報
- 書籍とジャンルの関連情報
- お気に入り情報
- レビュー情報
- レビューいいね情報
- 読書計画情報

## テスト用アカウント

Seeder実行後、以下のアカウントでログインできます。
読書計画など、ユーザーごとのデータおよび認可処理を確認する場合は、両方のアカウントを使用してください。

|メールアドレス       | パスワード |
|--------------------|-----------|
| yamada@example.com   | password |
| suzuki@example.com | password |

## 使用技術(実行環境)

- PHP 8.5
- Laravel 10.x
- MySQL 8.4
- Nginx
- Docker
- Laravel Sail
- Blade
- Tailwind CSS ^3.4.0
- @tailwindcss/forms
- Vite
- Laravel Sanctum
- PHPUnit
- JavaScript
- Google Books API
- phpMyAdmin


## APIエンドポイント一覧

| HTTPメソッド | URI | 説明 | 認証 |
|:---:|:---|:---|:---|
| GET | `/api/v1/books` | 書籍一覧を取得する | 不要 |
| GET | `/api/v1/books/{book}` | 書籍詳細を取得する | 不要 |
| POST | `/api/v1/books` | 書籍を新規登録する | ★ Sanctum必須 |
| PUT | `/api/v1/books/{book}` | 書籍を更新する | ★ Sanctum + BookPolicy（所有者のみ） |
| DELETE | `/api/v1/books/{book}` | 書籍を削除する | ★ Sanctum + BookPolicy（所有者のみ） |

## テスト

PHPUnitを使用しています。

### テスト実行

```bash
sail artisan test
```

### テスト内容

- 認証機能
- 書籍CRUD
- ジャンル機能
- お気に入り機能
- レビュー機能
- レビューいいね機能
- ランキング機能
- 検索・絞り込み・並び替え
- 読書計画
- 読書計画期限変更
- リマインダーバッチ
- 自動失効バッチ
- 通知機能
- 公開API
- Sanctum認証
- ISBN検索
- 読書レポート

## ER図

![ER図](src/app/docs/er-diagram advanced.md)

## URL

- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/

## 作成者
ami-takaoka
