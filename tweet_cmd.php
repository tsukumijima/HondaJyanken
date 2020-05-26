<?php

	// コマンドからツイートを投稿する用

	// TwitterOAuthの読み込み
	require ('twitteroauth/autoload.php');
	use Abraham\TwitterOAuth\TwitterOAuth;

	// 設定読み込み
	require (dirname(__FILE__).'/config.php');

	if (isset($OAUTH_TOKEN) and isset($OAUTH_TOKEN_SECRET)){ //アクセストークンがあれば

		// Twitterに接続
		$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $OAUTH_TOKEN, $OAUTH_TOKEN_SECRET);

		// ツイートの内容
		$tweet_text = $argv[1];

		// ツイートを送る
		$tweet = $connection->post('statuses/update', array('status' => $tweet_text));

		var_dump(array('status' => $tweet));

		// 結果
		$result = $connection->get('account/verify_credentials');

		// エラー確認
		if ($tweet and !isset($info->errors)){
			echo "\n".'ツイート：投稿成功っぽいです'."\n\n\n";
		} else {
			echo "\n".'ツイート：投稿失敗っぽいです'."\n\n\n";
		}

		echo '実行を完了しました。'."\n\n";

	} else { //セッションがない場合
		echo 'ツイートする場合はアクセストークンを設定してください。';
	}
