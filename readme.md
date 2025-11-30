# Europa Universalis V Wiki (System Repository)

このリポジトリは、**Europa Universalis V 日本語Wiki** のシステム部分（スキン、プラグイン、設定ファイル）を管理するものです。
記事データ等の「データ部分」は、セキュリティと管理の都合上、別のプライベートリポジトリで管理されています。

詳細な変更ファイル一覧やカスタマイズ履歴については、[**_changelog.md**](_changelog.md) を参照してください。

## 🎨 主な特徴 (Key Features)

*   **カスタムテーマ「王立古文書館」**: 羊皮紙をモチーフにした独自のCSSデザイン。
*   **システムとデータの分離**: Git管理において、システム（Public）とデータ（Private）を完全に分離した構成。
*   **Discord連携**: ページ更新時にDiscordサーバーへ自動通知。
*   **ユーザビリティ向上**: H6見出し対応、レスポンシブな目次サイドバー、GUI編集ボタンなど。
*   **独自プラグイン**: 高機能テーブル作成 (`flexlist`)、リダイレクト (`jump`) など多数。

## 🛠️ 環境構築 (Setup)

Windows (XAMPP) 上での開発と、Linux (Value Server) での運用を想定しています。

### 1. リポジトリのクローン
```bash
git clone https://github.com/YOUR_USERNAME/eu5-jp-wiki-system.git main
```

### 2. データディレクトリの準備
このリポジトリには記事データが含まれていません。[pukiwiki配布サイト](https://pukiwiki.sourceforge.io/?PukiWiki/Download/1.5.4)から以下のディレクトリを `C:\pukiwiki_data` (任意の場所) にコピーしてください。

*   `wiki/`
*   `attach/`
*   `backup/`
*   `diff/`
*   `counter/`
*   `cache/`
*   `trackback/`

### 3. ログディレクトリの作成
ログ保存用のフォルダはGit管理外ですが、動作には必要です。`main` フォルダの中に手動で作成してください。

```bash
mkdir log
```

### 4. シンボリックリンクの作成 (Windows/XAMPP)
管理者権限でコマンドプロンプトを開き、`main` ディレクトリ内で以下を実行してシステムとデータを結合します。

```cmd
mklink /D wiki C:\pukiwiki_data\wiki
mklink /D attach C:\pukiwiki_data\attach
mklink /D backup C:\pukiwiki_data\backup
mklink /D diff C:\pukiwiki_data\diff
mklink /D counter C:\pukiwiki_data\counter
mklink /D cache C:\pukiwiki_data\cache
mklink /D trackback C:\pukiwiki_data\trackback
```

### 5. 秘密設定ファイルの作成
パスワードなどの機密情報は `pukiwiki.ini.php` から分離されています。
ルートディレクトリに `pukiwiki.secret.php` を作成し、以下のように記述してください（このファイルはGit管理外です）。

```php
<?php
// 管理者パスワード設定
$adminpass = '{x-php-md5}あなたのパスワードハッシュ';
?>
```

---

## ⚠️ 注意事項 (Notes)
*   `/log` ディレクトリおよび `BingSiteAuth.xml` 等の認証ファイルは `.gitignore` により除外されています。
*   本番環境へのデプロイ時は、システムファイルのみをアップロードし、データディレクトリ（`wiki/`等）を上書きしないよう注意してください。
