<?php

	// 自動ツイートする用

	// TwitterOAuthの読み込み
	require ('twitteroauth/autoload.php');
	use Abraham\TwitterOAuth\TwitterOAuth;

	// 設定読み込み
	require (dirname(__FILE__).'/config.php');

	// アクセストークンがあれば
	if (isset($OAUTH_TOKEN) and isset($OAUTH_TOKEN_SECRET)){

		// Twitterに接続
		$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $OAUTH_TOKEN, $OAUTH_TOKEN_SECRET);

		// 天気予報APIを叩く
		$weather = json_decode(file_get_contents('http://weather.livedoor.com/forecast/webservice/json/v1?city=130010'), true);

		// パターンごとに
		switch ($weather['forecasts'][0]['telop']){
			
			case '晴れ':
				$telop = '☀️晴れ';
			break;

			case '曇り':
				$telop = '☁️曇り';
			break;

			case '雨':
				$telop = '☁️雨';
			break;

			case '雪':
				$telop = '⛄雪';
			break;

			case '晴時々曇':
				$telop = '⛅️晴れ時々曇り';
			break;

			case '曇時々晴':
				$telop = '⛅️曇り時々晴れ';
			break;

			case '雨時々曇':
				$telop = '🌧️雨時々曇り';
			break;

			case '曇時々雨':
				$telop = '🌧️曇り時々雨';
			break;

			case '雪時々曇':
				$telop = '🌨️雪時々曇り';
			break;

			case '曇時々雪':
				$telop = '🌨️曇り時々雪';
			break;

			case '晴のち曇':
				$telop = '☀️➡☁️晴れのち曇り';
			break;

			case '曇のち晴':
				$telop = '☁️➡☀️曇りのち晴れ';
			break;

			case '雨のち曇':
				$telop = '☂️➡☁️雨のち曇り';
			break;

			case '曇のち雨':
				$telop = '☁️➡☂️曇りのち雨';
			break;

			case '雪のち曇':
				$telop = '⛄️➡☁️雪のち曇り';
			break;

			case '曇のち雪':
				$telop = '☁️➡⛄️曇りのち雪';
			break;

			default:
				$telop = $weather['forecasts'][0]['telop'];
			break;

		}

		// ツイートの内容
		$tweet_text = '🗓'.date('Y年m月d日').'の朝です。おはようございます。'."\n".
					  '東京の天気は'.$telop.'、最高気温は🌡️'.@$weather['forecasts'][0]['temperature']['max']['celsius'].'℃です。'."\n".
					  '今日も #本田とじゃんけん か #本田とカードバトル 、やりませんか？'."\n".
					  'そしたら今回も、俺が勝ちますよ。'."\n".
					  '運も実力のうち。ほな、いただきます。'."\n";

		// 画像を送る
		$media = $connection->upload('media/upload', ['media' => dirname(__FILE__).'/image/honda_pepsi.jpg', 'media_type' => 'image/jpeg'], true);

		// ツイートを送る
		$tweet = $connection->post('statuses/update', array(
			'status' => $tweet_text,
			'media_ids' => implode(',', [$media->media_id_string])
		));

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
