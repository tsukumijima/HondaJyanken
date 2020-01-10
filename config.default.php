<?php

// ***** 一般設定 *****

// スクリーンネーム(アカウント名)
$screen_name = '本田とじゃんけんBot(非公式)';

// HTMLから参照する実行ログのファイル名
// ログは別途 Cron に */1 * * * * php /root/HondaJyanken/tweet.php > /root/HondaJyanken/exec.log 2>&1
// のように設定して予め吐き出すようにする(Cronにはフルパスで設定)
$logfile = 'exec.log';

// バージョン
$version = 'v3.0';


// ***** Twitter API 設定 ****

// コンシューマーキー
$CONSUMER_KEY =  'YOUR_CONSUMER_KEY';
// コンシューマーシークレットキー
$CONSUMER_SECRET = 'YOUR_CONSUMER_SECRET';

// アクセストークン
$OAUTH_TOKEN = 'YOUR_ACCESS_TOKEN';
// アクセストークンシークレット
$OAUTH_TOKEN_SECRET = 'YOUR_ACCESS_TOKEN_SECRET';
