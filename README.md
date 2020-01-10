# HondaJyanken

本田とじゃんけんBot (@HondaJyanken) 用のプログラム一式です

説明書は /root/HondaJyanken/ にインストールした前提で書かれています  
他のフォルダに入れた場合は適宜読み替えてください

## 内容物

- 334.php … 334用
- config.default.php … 設定ファイル
  - 手動で config.php にコピーしてください
- index.php … Web からログとか確認したり Bot 叩けるようにするため用
  - もし Web から叩けるようにしたい場合はこのプログラムを Web 公開用ディレクトリの中に入れる必要があります
- README.md … この説明書
- tweet.php … Bot 本体
  - このファイルを Cron で叩くことで Bot を実行します
- tweet_auto.php … 自動ツイート用
  - 毎日8時に今日の東京の天気を呟けるようにしてあります
- tweet_cmd.php … コマンドからツイートを投稿する用
  - `php /root/HondaJyanken/tweet_auto.php "おはようございます"` とコマンドを実行すると「おはようございます」とツイートされます

## 使い方

1. config.default.php を config.php にコピーする
2. コンシューマーキーやアクセストークン等を config.php に設定する
3. Cron に `*/1 * * * * php /root/HondaJyanken/tweet.php > /root/HondaJyanken/exec.log 2>&1` のように登録する
4. 自動ツイートをしたければ Cron に ``0 8 * * * php /root/HondaJyanken/tweet_auto.php > /root/HondaJyanken/exec.log 2>&1` のように登録する
5. 後は Cron が自動で1秒ごとにBotプログラムを叩いてくれるはず
   - 動かない場合はコンシューマーキー・アクセストークン等が合っているかどうか確認してください
   - また、ログも確認してみてください

## License
[MIT Licence](LICENSE.txt)
