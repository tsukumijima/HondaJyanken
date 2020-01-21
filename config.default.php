<?php

// ***** 一般設定 *****

// スクリーンネーム(アカウント名)
$screen_name = 'HondaJyanken';

// HTMLから参照する実行ログのファイル名
// ログは別途 Cron に */1 * * * * php /root/HondaJyanken/tweet.php > /root/HondaJyanken/exec.log 2>&1
// のように設定して予め吐き出すようにする(Cronにはフルパスで設定)
$logfile = 'exec.log';

// 前回の実行時間を記録するファイルのファイル名
$timefile = 'time.log';

// バージョン
$version = 'v3.1';


// ***** Twitter API 設定 ****

// コンシューマーキー
$CONSUMER_KEY =  'YOUR_CONSUMER_KEY';
// コンシューマーシークレットキー
$CONSUMER_SECRET = 'YOUR_CONSUMER_SECRET';

// アクセストークン
$OAUTH_TOKEN = 'YOUR_ACCESS_TOKEN';
// アクセストークンシークレット
$OAUTH_TOKEN_SECRET = 'YOUR_ACCESS_TOKEN_SECRET';
