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

	// アクセストークンがあれば
	if (isset($OAUTH_TOKEN) and isset($OAUTH_TOKEN_SECRET)){

		// ヘッダを設定
		header('content-type: application/json; charset=utf-8');

		// 日時関連
		$timestamp = microtime(true); // 現在時刻のタイムスタンプ

		// 何分前までのツイートを取得するか
		if (file_exists(dirname(__FILE__).'/'.$timefile)){
			// 現在のUnix時間から前回実行したUnix時間を引く
			$last_timestamp = file_get_contents(dirname(__FILE__).'/'.$timefile);
			$last = $timestamp - $last_timestamp; // 秒
		} else {
			// 20秒前に実行したと仮定
			$last = 20; // 秒
			$last_timestamp = $timestamp - $last;
		}

		// 表示
		echo "\n";
		echo '  -------------------------------------------------------'."\n";
		echo '            ***** 本田とじゃんけんBot '.$version.' *****'."\n";
		echo '  -------------------------------------------------------'."\n\n";
		echo '    実行時刻       : '.date('Y-m-d H:i:s')."\n";
		echo '    前回の実行時刻 : '.date('Y-m-d H:i:s', $last_timestamp)."\n";
		echo '    実行間隔       : '.$last.' 秒'."\n\n";
		echo '  -------------------------------------------------------'."\n\n";

		// Twitterに接続
		$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $OAUTH_TOKEN, $OAUTH_TOKEN_SECRET);

		// 毎時0分・30分なら自動フォローする
		$min = date('i');
		if ($min == '00' or $min == '30'){
			$followers = $connection->get('followers/ids', array('cursor' => -1)); // フォロワーを取得
			$friends = $connection->get('friends/ids', array('cursor' => -1)); // フォロー中
			echo '    自動フォロバを開始します。'."\n\n";
			foreach ($followers->ids as $i => $id) {
				if (empty($friends->ids) or !in_array($id, $friends->ids)) {
					$connection->post('friendships/create', array('user_id' => $id)); // フォロバする
					echo '    フォロバしたユーザーID: '.$id."\n";
				}
			}
			echo "\n";
			echo '    自動フォロバを終了します。'."\n\n";
			echo '  -------------------------------------------------------'."\n\n";
		}
		
		echo '    リプライを取得します。'."\n";

		// リプライを取得
		$res = $connection->get('statuses/mentions_timeline', array('count' => $count));

		// 実行時間を記録する
		file_put_contents(dirname(__FILE__).'/'.$timefile, microtime(true));

		echo '    直近'.(count($res)).'件のツイートを取得しました。'."\n\n";
		echo '    リプライへの返信を開始します。'."\n\n";
		echo '  -------------------------------------------------------'."\n\n";

		// ツイートを取り出す
		for ($i = 0; $i < count($res); $i++) {

			// タイムスタンプを整形&修正
			$tweet_timestamp = strtotime($res[$i]->created_at); // ツイート投稿時刻のタイムスタンプが手に入る

			echo '    ツイート投稿日時: '.date('Y-m-d H:i:s', $tweet_timestamp).' ツイートのタイムスタンプ: '.$tweet_timestamp."\n";
			echo '    ツイートのURL: https://twitter.com/i/status/'.$res[$i]->id."\n\n";

			// 自分からのリプライではない & pepsi_jpn を除外 & ツイート投稿時刻が前回の実行時刻よりも後なら
			if ($res[$i]->user->screen_name !== $screen_name and $res[$i]->user->screen_name !== 'pepsi_jpn' and $tweet_timestamp >= $last_timestamp){

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

							echo '    #本田にグーで勝つ: 残念、本田圭佑の勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_lost_3.mp4';

							// リプライの内容
							$reply = '残念、本田圭佑の勝利！'."\n\n"
									.'YOU LOSE!!!! 俺の勝ち！😁😁'."\n"
									.'負けは次に繋がるチャンスです。ネバーギブアップ！'."\n"
									.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
									.'一日何回でも勝負。'."\n"
									.'じゃあ、また今度👋☺️'."\n";

							break;

						// 買った場合
						case 'YOU WIN':

							echo '    #本田にグーで勝つ: お見事！あなたの勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_win_2.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
									.'YOU WIN!!!! やるやん。'."\n"
									.'明日は俺にリベンジさせて。'."\n"
									.'では、どうぞ(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
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

							echo '    #本田にチョキで勝つ: 残念、本田圭佑の勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_lost_1.mp4';

							// リプライの内容
							$reply = '残念、本田圭佑の勝利！'."\n\n"
									.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
									.'たかがじゃんけん、そう思ってないですか？'."\n"
									.'それやったら明日も、俺が勝ちますよ。'."\n"
									.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
									.'一日何回でも勝負。'."\n"
									.'じゃあ、また今度👋☺️'."\n";

							break;

						// 買った場合
						case 'YOU WIN':

							echo '    #本田にチョキで勝つ: お見事！あなたの勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_win_3.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
									.'YOU WIN!!!! やるやん。'."\n"
									.'明日は俺にリベンジさせて。'."\n"
									.'では、どうぞ(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
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

							echo '    #本田にパーで勝つ: 残念、本田圭佑の勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_lost_2.mp4';

							// リプライの内容
							$reply = '残念、本田圭佑の勝利！'."\n\n"
									.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
									.'何で負けたか明日までに考えといてください。そしたら何かが見えてくるはずです。'."\n"
									.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
									.'一日何回でも勝負。'."\n"
									.'じゃあ、また今度👋☺️'."\n";

							break;

						// 買った場合
						case 'YOU WIN':

							echo '    #本田にパーで勝つ: お見事！あなたの勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_win_1.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
									.'YOU WIN!!!! やるやん。'."\n"
									.'明日は俺にリベンジさせて。'."\n"
									.'では、どうぞ(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
									.'一日何回でも勝負。'."\n"
									.'じゃあ、また今度👋☺️'."\n";

							break;
					}
				}

				
				// 取得したツイートに #私は本田のAを引く が含まれていたら
				if (strpos($res[$i]->text, 'Aを引く') !== false){

					// 勝敗判定
					switch($jyanken) {

						case 'YOU LOSE':

							// パターンをランダムで選ぶ
							$you_lose = rand(1, 4); // 1～4の乱数を取得

							echo '    #私は本田のAを引く: 残念、本田圭佑の勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_card_lost_A'.$you_lose.'.mp4';

							// パターンによって文言を変える
							switch($you_lose) {

								case 1:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
											.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
											.'カードの向こう側に何があるか、考えてみてください。'."\n"
											.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
											.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
											.'また今度👋☺️'."\n";

									break;

								case 2:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
											.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
											.'ケイスケ ホンダの心なんて読めるわけない、そう思ってないですか？'."\n"
											.'あきらめへん人だけに見える景色があるはずです。'."\n"
											.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
											.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
											.'また今度👋☺️'."\n";

									break;

								case 3:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
											.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
											.'ウラのウラのウラまで、読む訓練をしてくださいね。'."\n"
											.'どこまで読もうとするかで、結果が変わってきます。'."\n"
											.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
											.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
											.'また今度👋☺️'."\n";

									break;

								case 4:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
											.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
											.'ウラのウラのウラまで、読む訓練をしてくだいね。'."\n"
											.'どこまで読もうとするかで、結果が変わってきます。'."\n"
											.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
											.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
											.'また今度👋☺️'."\n";

									break;
							}

							break;

						case 'YOU WIN':

							echo '    #私は本田のAを引く: お見事！あなたの勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_card_win_A.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
									.'YOU WIN!!!! 俺の負け！'."\n"
									.'やるやん！'."\n"
									.'でも、今度は絶対、俺が勝つから！'."\n"
									.'また明日やろう！(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
									.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
									.'また今度👋☺️'."\n";

							break;
					}
				}

				// 取得したツイートに #私は本田のBを引く が含まれていたら
				if (strpos($res[$i]->text, 'Bを引く') !== false){

					// 勝敗判定
					switch($jyanken) {

						case 'YOU LOSE':

							// パターンをランダムで選ぶ
							$you_lose = rand(1, 3); // 1～3の乱数を取得

							echo '    #私は本田のBを引く: 残念、本田圭佑の勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_card_lost_B'.$you_lose.'.mp4';

							// パターンによって文言を変える
							switch($you_lose) {

								case 1:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
											.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
											.'どんな事でも、絶対に勝つんや！というメンタリティーが大事ですよ。'."\n"
											.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
											.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
											.'また今度👋☺️'."\n";

									break;

								case 2:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
											.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
											.'自信を持って、勝負にしっかりと向き合える。'."\n"
											.'そう思えるまで、準備してください。'."\n"
											.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
											.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
											.'また今度👋☺️'."\n";

									break;

								case 3:

									// リプライの内容
									$reply = '残念、本田圭佑の勝利！'."\n\n"
											.'YOU LOSE!!!! 俺の勝ち！😁'."\n"
											.'複雑に考えてないですか？答えはシンプルです。'."\n"
											.'ケイスケ ホンダの心を読む、それだけです。'."\n"
											.'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
											.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
											.'また今度👋☺️'."\n";

									break;
							}

							break;

						case 'YOU WIN':

							echo '    #私は本田のBを引く: お見事！あなたの勝利！'."\n\n";

							// アップロードする動画
							$video = dirname(__FILE__).'/video/honda_card_win_B.mp4';

							// リプライの内容
							$reply = 'お見事！あなたの勝利！'."\n\n"
									.'YOU WIN!!!! 俺の負け！'."\n"
									.'やるやん！'."\n"
									.'でも、今度は絶対、俺が勝つから！'."\n"
									.'また明日やろう！(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n"
									.'(ｯﾊｰ)うまい！1日何回でも勝負。'."\n"
									.'また今度👋☺️'."\n";

							break;
					}
				}

				// video が NULL でないなら
				if (isset($video)){

					// Twitterに動画をアップロード
					$media = $connection->upload('media/upload', ['media' => $video, 'media_type' => 'video/mp4'], true);

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
					
					$reply_array = array(
						'status' => '@'.$res[$i]->user->screen_name."\n".$reply,
						'media_ids' => implode(',', [$media->media_id_string]),
						'in_reply_to_status_id' => $res[$i]->id,
					);

				// 有効なコマンドが含まれていなかったときはヘルプを送信する
				// その他のリプライ全てに返信すると過剰なので # と 本田 が含まれる場合に限る
				} else if (strpos($res[$i]->text, '#') !== false and
						   strpos($res[$i]->text, '本田') !== false and
						   strpos($res[$i]->text, 'じゃあ、また今度') === false){

					echo '    コマンドが指定されていないため、ヘルプを送信します。'."\n\n";

					// リプライの内容
					$reply = 'コマンドが指定されていません。'."\n\n"
							.'じゃんけんするなら「@HondaJyanken #本田に(グー or チョキ or パー)で勝つ」とツイート、'."\n"
							.'カードバトルするなら「@HondaJyanken #私は本田の(A or B)を引く」とツイートしといてください。'."\n\n"
							.'1日何回でも勝負。'."\n"
							.'じゃあ、もう一度👋☺️'."\n";
					
					$reply_array = array(
						'status' => '@'.$res[$i]->user->screen_name."\n".$reply,
						'in_reply_to_status_id' => $res[$i]->id,
					);

				}

				// リプライを送る
				$rp = $connection->post('statuses/update', $reply_array);
				
				// ファボる
				$fav = $connection->post('favorites/create', array('id' => $res[$i]->id));

				// 送信結果
				$result = $connection->get('account/verify_credentials');

				// エラー確認
				if ($rp and !isset($info->errors)){
					echo '    '.($i+1).'番目のツイート: 結果をツイートしました。'."\n\n";
				} else {
					echo '    '.($i+1).'番目のツイート: 結果のツイートに失敗しました…'."\n\n";
				}

				// ヘルプを続けて送信する
				if (!isset($video)){
					
					// リプライの内容
					$reply = '勝率は本家よりも高めの10%ですが、ツイートに #本田優しくして とつけると勝率が40%に、#本田大好き とつけると勝率が70%に上がります。'."\n\n"
							.'どうしても勝てない時、試しといてください。'."\n"
							.'ほな、(勝利) いただきます😁'."\n\n";
					
					$reply_array = array(
						'status' => $reply,
						'in_reply_to_status_id' => $rp->id,
					);

					// リプライを送る
					$rp = $connection->post('statuses/update', $reply_array);

				}

			} else if ($tweet_timestamp < $last_timestamp){

				echo '    '.($i+1).'番目のツイート: 前回の実行時刻よりも前にツイートされているため、スキップします。'."\n\n";

			} else if ($res[$i]->user->screen_name == $screen_name){

				echo '    '.($i+1).'番目のツイート: 自分からのリプライのため、スキップします。'."\n\n";

			}
	
			echo '  -------------------------------------------------------'."\n\n";
		}

		echo '    リプライを終了します。'."\n\n";
		echo '    実行を完了しました。終了します。'."\n\n";
		echo '  -------------------------------------------------------'."\n\n";

	} else { //セッションがない場合
		echo 'ツイートさせるにはアクセストークンを設定してください。';
	}

