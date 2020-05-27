# HondaJyanken

本田とじゃんけんBot (@HondaJyanken) 用のプログラム一式

この説明書は Linux 環境にて /root/HondaJyanken/ に配置することを前提とする  
他のフォルダに入れた場合や Windows 環境で動かす場合は適宜読み替えること  
Bot 制作に役立てるなりご自由に

## 内容物

- README.md … この説明書
- config.default.php … 設定ファイル
  - 手動で config.php にコピーしてください
- 334.php … 334用
- tweet.php … Bot 本体
  - このファイルを Cron で定期的に叩くことで Bot を実行する
- tweet_auto.php … 自動ツイート用
  - 今日の東京の天気と画像を呟けるようにしてある
- tweet_cmd.php … コマンドからツイートを投稿する用
  - `php /root/HondaJyanken/tweet_auto.php "おはようございます"` とコマンドを実行するとツイートされる
- twitteroauth/ … Twitter API 操作用ライブラリ
- video/ … 投稿する動画

## 使い方

1. お使いの PC or サーバーに PHP が入っていない場合は適宜インストールする
2. config.default.php を config.php にコピーする
3. Twitter API のコンシューマーキー・アクセストークンなどを config.php に設定する
4. Cron に `*/1 * * * * php /root/HondaJyanken/tweet.php >> /root/HondaJyanken/exec.log 2>&1` のように登録する
   - ログは追記したほうが不具合確認がしやすいがずっと消さないと肥大化するので定期的に消すこと
5. 自動ツイートをしたければ Cron に ``0 8 * * * php /root/HondaJyanken/tweet_auto.php` のように登録する
6. 後は Cron が自動で1秒ごとに Bot を叩いてくれるはず
   - 動かない場合はコンシューマーキー・アクセストークンが合っているかどうかや出力されたログを確認すること

## License
[MIT Licence](LICENSE.txt)
