<?php

	// じゃんけんのリプライ用

	// TwitterOAuthの読み込み
	require ('twitteroauth/autoload.php');
	use Abraham\TwitterOAuth\TwitterOAuth;

	// 設定読み込み
	require (dirname(__FILE__).'/config.php');

	// 設定
	// 勝利確率
	$percent = 10; // %(パーセント)

	// ツイートを何個取得するか
	$count = 10; // 件

	// 何分前までのツイートを取得するか
	$last = 0.334; // 分

	// アクセストークンがあれば
	if (isset($OAUTH_TOKEN) and isset($OAUTH_TOKEN_SECRET)){

		// ヘッダを設定
		header('content-type: application/json; charset=utf-8');

		// 日時関連
		$timestamp = time(); // 現在時刻のタイムスタンプ
		$last_timestamp = $timestamp - $last * 60; // $last分前のタイムスタンプ

		// 表示
		echo '本田とじゃんけんBot '.$version."\n\n";
		echo '実行時刻: '.date('Y-m-d H:i:s')."\n";
		echo '現在のタイムスタンプ: '.$timestamp.' '.$last.'分前のタイムスタンプ: '.$last_timestamp."\n\n";

		// Twitterに接続
		$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $OAUTH_TOKEN, $OAUTH_TOKEN_SECRET);

		// 毎時0分・30分なら自動フォローする
		$min = intval(date('i'));
		if ($min == 00 or $min == 30){
			$followers = $connection->get('followers/ids', array('cursor' => -1)); // フォロワーを取得
			$friends = $connection->get('friends/ids', array('cursor' => -1)); // フォロー中
	 
			echo '自動フォロバを開始します。'."\n\n";
			foreach ($followers->ids as $i => $id) {
				if (empty($friends->ids) or !in_array($id, $friends->ids)) {
					$connection->post('friendships/create', array('user_id' => $id)); // フォロバする
					echo 'フォロバしたユーザーID: '.$id."\n";
				}
			}
			echo "\n".'自動フォロバを終了します。'."\n\n";
		}

		// リプライを取得
		$res = $connection->get('statuses/mentions_timeline', array('count' => $count));

		// var_dump($res); // 生ログ

		echo (count($res)).'件のツイートを取得しました。'."\n\n";
		echo 'リプライを開始します。'."\n\n";

		// ツイートを取り出す
		for ($i = 0; $i < count($res); $i++) {

			// タイムスタンプを整形&修正
			$tweet_timestamp = strtotime($res[$i]->created_at); // ツイート投稿時刻のタイムスタンプが手に入る

			// 投稿時間を整形して表示
			echo 'ツイート投稿日時: '.date('Y-m-d H:i:s', $tweet_timestamp).' ツイートのタイムスタンプ: '.$tweet_timestamp."\n";
			echo 'ツイートのURL: https://twitter.com/i/status/'.$res[$i]->id."\n\n";

			// 自分のツイートじゃない +
			// ツイート投稿時刻が$last分前以内なら
			if ($res[$i]->user->screen_name !== $screen_name and $tweet_timestamp >= $last_timestamp){

				// ログ表示
				echo 'ツイート: '."\n";
				//print_r($res[$i]);

				// #本田優しくして
				if (strpos($res[$i]->text, '#本田優しくして') !== false){
					$percent = 40;
				}
				// #本田大好き
				if (strpos($res[$i]->text, '#本田大好き') !== false){
					$percent = 70;
				}

				// 勝敗を決める
				$random = rand(1, 100); // 1～100の乱数を取得

				if ($random < $percent){ // 1～指定確率の間なら
					$jyanken = 'YOU WIN';
				} else {
					$jyanken = 'YOU LOSE';
				}

				// 取得したツイートに #本田にグーで勝つ が含まれていたら
				if (strpos($res[$i]->text, 'グー') !== false){

					// 勝敗判定
					switch($jyanken) {

						// 負けた場合
						case 'YOU LOSE':

							echo 'グーで勝つ：残念、本田圭佑の勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_lost_3.mp4';

							// リプライの内容
							$reply = '残念、本田圭佑の勝利！'."\n\n"
								.'YOU LOSE!!!! 俺の勝ち！😁😁'."\n"
								.'負けは次に繋がるチャンスです。ネバーギブアップ！'."\n"
								.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
								.'一日何回でも勝負。'."\n"
								.'じゃあ、また今度👋☺️'."\n";

							break;

						// 買った場合
						case 'YOU WIN':

							echo 'グーで勝つ：お見事！あなたの勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_win_2.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
								.'YOU WIN!!!! やるやん。'."\n"
								.'明日は俺にリベンジさせて。'."\n"
								.'では、どうぞ(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
								.'一日何回でも勝負。'."\n"
								.'じゃあ、また今度👋☺️'."\n";

							break;
					}
				}

				// 取得したツイートに #本田にチョキで勝つ が含まれていたら
				if (strpos($res[$i]->text, 'チョキ') !== false){

					// 勝敗判定
					switch($jyanken) {

						// 負けた場合
						case 'YOU LOSE':

							echo 'チョキで勝つ：残念、本田圭佑の勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_lost_1.mp4';

							// リプライの内容
							$reply = '残念、本田圭佑の勝利！'."\n\n"
								.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
								.'たかがじゃんけん、そう思ってないですか？'."\n"
								.'それやったら明日も、俺が勝ちますよ。'."\n"
								.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
								.'一日何回でも勝負。'."\n"
								.'じゃあ、また今度👋☺️'."\n";

							break;

						// 買った場合
						case 'YOU WIN':

							echo 'チョキで勝つ：お見事！あなたの勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_win_3.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
								.'YOU WIN!!!! やるやん。'."\n"
								.'明日は俺にリベンジさせて。'."\n"
								.'では、どうぞ(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
								.'一日何回でも勝負。'."\n"
								.'じゃあ、また今度👋☺️'."\n";

							break;
					}
				}

				// 取得したツイートに #本田にパーで勝つ が含まれていたら
				if (strpos($res[$i]->text, 'パー') !== false){

					// 勝敗判定
					switch($jyanken) {

						// 負けた場合
						case 'YOU LOSE':

							echo 'パーで勝つ：残念、本田圭佑の勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_lost_2.mp4';

							// リプライの内容
							$reply = '残念、本田圭佑の勝利！'."\n\n"
							.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
							.'何で負けたか明日までに考えといてください。そしたら何かが見えてくるはずです。'."\n"
							.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
							.'一日何回でも勝負。'."\n"
							.'じゃあ、また今度👋☺️'."\n";

							break;

						// 買った場合
						case 'YOU WIN':

							echo 'パーで勝つ：お見事！あなたの勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_win_1.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
								.'YOU WIN!!!! やるやん。'."\n"
								.'明日は俺にリベンジさせて。'."\n"
								.'では、どうぞ(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
								.'一日何回でも勝負。'."\n"
								.'じゃあ、また今度👋☺️'."\n";

							break;
					}
				}

				
				// 取得したツイートに #私は本田のAを引く が含まれていたら
				if (strpos($res[$i]->text, 'Aを引く') !== false){

					// 勝敗判定
					switch($jyanken) {

						// 負けた場合
						case 'YOU LOSE':

							// パターンをランダムで選ぶ
							$you_lose = rand(1, 4); // 1～4の乱数を取得

							echo '私は本田のAを引く：残念、本田圭佑の勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_card_lost_A'.$you_lose.'.mp4';

							// パターンによって文言を変える
							switch($you_lose) {

								case 1:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
										.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
										.'カードの向こう側に何があるか、考えてみてください。'."\n"
										.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
										.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
										.'また今度👋☺️'."\n";

									break;

								case 2:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
										.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
										.'ケイスケ ホンダの心なんて読めるわけない、そう思ってないですか？'."\n"
										.'あきらめへん人だけに見える景色があるはずです。'."\n"
										.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
										.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
										.'また今度👋☺️'."\n";

									break;

								case 3:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
										.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
										.'ウラのウラのウラまで、読む訓練をしてくださいね。'."\n"
										.'どこまで読もうとするかで、結果が変わってきます。'."\n"
										.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
										.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
										.'また今度👋☺️'."\n";

									break;

								case 4:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
										.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
										.'ウラのウラのウラまで、読む訓練をしてくだいね。'."\n"
										.'どこまで読もうとするかで、結果が変わってきます。'."\n"
										.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
										.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
										.'また今度👋☺️'."\n";

									break;
							}

							break;

						// 買った場合
						case 'YOU WIN':

							echo '私は本田のAを引く：お見事！あなたの勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_card_win_A.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
								.'YOU WIN!!!! 俺の負け！'."\n"
								.'やるやん！'."\n"
								.'でも、今度は絶対、俺が勝つから！'."\n"
								.'また明日やろう！(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
								.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
								.'また今度👋☺️'."\n";

							break;
					}
				}

				// 取得したツイートに #私は本田のBを引く が含まれていたら
				if (strpos($res[$i]->text, 'Bを引く') !== false){

					// 勝敗判定
					switch($jyanken) {

						// 負けた場合
						case 'YOU LOSE':

							// パターンをランダムで選ぶ
							$you_lose = rand(1, 3); // 1～3の乱数を取得

							echo '私は本田のBを引く：残念、本田圭佑の勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_card_lost_B'.$you_lose.'.mp4';

							// パターンによって文言を変える
							switch($you_lose) {

								case 1:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
										.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
										.'どんな事でも、絶対に勝つんや！というメンタリティーが大事ですよ。'."\n"
										.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
										.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
										.'また今度👋☺️'."\n";

									break;

								case 2:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
										.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
										.'自信を持って、勝負にしっかりと向き合える。'."\n"
										.'そう思えるまで、準備してください。'."\n"
										.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
										.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
										.'また今度👋☺️'."\n";

									break;

								case 3:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
										.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
										.'複雑に考えてないですか？答えはシンプルです。'."\n"
										.'ケイスケ ホンダの心を読む、それだけです。'."\n"
										.'ほな、いただきます(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
										.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
										.'また今度👋☺️'."\n";

									break;
							}

							break;

						// 買った場合
						case 'YOU WIN':

							echo '私は本田のBを引く：お見事！あなたの勝利！：'."\n".$res[$i]->text."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_card_win_B.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
								.'YOU WIN!!!! 俺の負け！'."\n"
								.'やるやん！'."\n"
								.'でも、今度は絶対、俺が勝つから！'."\n"
								.'また明日やろう！(ﾌﾞｼｭｳｳｳｳｳｳｳ)'."\n\n"
								.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
								.'また今度👋☺️'."\n";

							break;
					}
				}

				// Twitterに動画をアップロード
				$media = $connection->video('media/video', ['media' => $video, 'media_type' => 'video/mp4'], true);

				// #本田優しくして か #本田大好き が入っていたら
				if ($percent == 40 or $percent == 70){

					switch($jyanken) {

						case 'YOU LOSE':
							$reply = str_replace('YOU LOSE!!!!', 'まだまだ甘いです。YOU LOSE!!!!', $reply);
							break;

						case 'YOU WIN':
							$reply = str_replace('YOU WIN!!!!', '勝ててよかったな。YOU WIN!!!!', $reply);
							break;

					}

				}

				// リプライを送る
				$rp = $connection->post('statuses/update', array(
					'status' => '@'.$res[$i]->user->screen_name."\n".$reply,
					'media_ids' => implode(',', [$media->media_id_string]),
					'in_reply_to_status_id' => $res[$i]->id,
				));

				var_dump(array(
					'status' => '@'.$res[$i]->user->screen_name."\n".$reply,
					'media_ids' => implode(',', [$media->media_id_string]),
					'in_reply_to_status_id' => $res[$i]->id,
				));
				
				// ファボる
				$fav = $connection->post('favorites/create', array('id' => $res[$i]->id));

				// 結果
				$result = $connection->get('account/verify_credentials');

				// エラー確認
				if ($rp and !isset($info->errors)){
					echo "\n".($i+1).'番目のツイート：投稿成功っぽいです'."\n\n\n";
				} else {
					echo "\n".($i+1).'番目のツイート：投稿失敗っぽいです'."\n\n\n";
				}
			}
		}

		echo 'リプライを終了します。'."\n\n";
		echo '実行を完了しました。'."\n\n";

	} else { //セッションがない場合
		echo 'ツイートさせるにはアクセストークンを設定してください。';
	}

