# database_practice
# DB作成手順

### 操作手順（毎回使う流れ）

MySQLサーバーを起動しておいてください(port:3306)

まずrootユーザにログインします

```bash
bash
# MySQLへ接続（MAMP）
mysql -u root -p -P 3306
# パスワードは root（MAMPデフォルト）
```

今回のアプリ用に新規にユーザーを作成します

```sql
sql
CREATE USER 'quiz_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON quiz_app.* TO 'quiz_user'@'localhost';
FLUSH PRIVILEGES;
```

exitでmysqlから抜け、先ほど作成したユーザーでmysqlへ再接続

```bash
bash
# MySQLへ接続（MAMP）
mysql -u quiz_user -p -P 3306
# 先ほど作成したユーザー名でログイン
```

database_pradtice直下のquiz_app.sqlを実行でDB作成完了