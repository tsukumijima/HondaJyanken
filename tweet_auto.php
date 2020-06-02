<?php

	// 自動ツイートする用

	// TwitterOAuthの読み込み
	require ('twitteroauth/autoload.php');
	use Abraham\TwitterOAuth\TwitterOAuth;

	// 設定読み込み
	require (dirname(__FILE__).'/config.php');

	// アクセストークンがあれば
	if (isset($config['OAUTH_TOKEN']) and isset($config['OAUTH_TOKEN_SECRET'])){

		// Twitterに接続
		$connection = new TwitterOAuth($config['CONSUMER_KEY'], $config['CONSUMER_SECRET'], $config['OAUTH_TOKEN'], $config['OAUTH_TOKEN_SECRET']);

		// 天気予報APIを叩く
		$weather = json_decode(file_get_contents('http://weather.livedoor.com/forecast/webservice/json/v1?city=130010'), true);

		// パターンごとに
		if (isset($weather['forecasts'][0]['telop'])) {

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

		} else {

			$telop = '--';

		}

		// 気温
		if (isset($weather['forecasts'][0]['temperature']['max']['celsius'])) {
			$temperature = $weather['forecasts'][0]['temperature']['max']['celsius'];
		} else {
			$temperature = '--';
		}

		// ツイートの内容
		$tweet_text = '🗓'.date('Y/m/d').'の朝です。おはようございます。'."\n".
					  '東京の天気は'.$telop.'、最高気温は🌡️'.$temperature.'℃です。'."\n".
					  '今日も #本田とじゃんけん / #本田とじゃんけん2020 / #本田とカードバトル / #本田とコイントス 、やりませんか？'."\n".
					  'そしたら今回も、俺が勝ちますよ。'."\n".
					  '運も実力のうち。ほな、いただきます。'."\n";

		// 画像をランダムで選択
		$image_glob = glob(dirname(__FILE__).'/image/*');
		$image_key = array_rand($image_glob);
		$image = $image_glob[$image_key];
		$image_mime = mime_content_type($image);

		// 画像を送る
		$media = $connection->upload('media/upload', ['media' => $image, 'media_type' => $image_mime], true);

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
