<?php

// 334用

// TwitterOAuthの読み込み
require ('twitteroauth/autoload.php');
use Abraham\TwitterOAuth\TwitterOAuth;

// 設定読み込み
require (dirname(__FILE__).'/config.php');

// Twitterに接続
$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $OAUTH_TOKEN, $OAUTH_TOKEN_SECRET);

// ツイートを送る
$tweet = $connection->post('statuses/update', array('status' => '334'));
