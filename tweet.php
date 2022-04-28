<?php

    // *** じゃんけん・カードバトル・コイントスを行いリプライを返すスクリプト ***

    // TwitterOAuth の読み込み
    require_once ('twitteroauth/autoload.php');
    use Abraham\TwitterOAuth\TwitterOAuth;

    // 設定読み込み
    require_once (dirname(__FILE__).'/config.php');

    $honda = new Honda($config);

    class Honda {

        // 設定
        public $config;

        // Twitter API のコネクション
        public $connection;

        // 勝利確率
        public $percentage;

        // 勝敗
        public $battle;

        // 俺の勝ち (あなたの負け)
        public const YOU_LOSE = false;

        // 俺の負け (あなたの勝ち)
        public const YOU_WIN = true;

        // #本田優しくして
        public const HONDA_KIND = 40;

        // #本田大好き
        public const HONDA_LOVE = 70;


        /**
         * コンストラクタ
         */
        public function __construct($config){

            // 設定をメンバ変数に代入
            $this->config = $config;


            // 現在の実行時刻のタイムスタンプ
            $timestamp = microtime(true);

            // 何分前までのツイートを取得するか
            if (file_exists(dirname(__FILE__).'/'.$this->config['time_log'])){

                // 前回の実行時刻のタイムスタンプ
                $timestamp_last = file_get_contents(dirname(__FILE__).'/'.$this->config['time_log']);
                // 実行間隔
                $interval = $timestamp - $timestamp_last; // 秒

            } else {

                // 1時間前に実行したと仮定
                $interval = 3600; // 秒
                $timestamp_last = $timestamp - $interval;

            }

            // 実行時間を記録する
            file_put_contents(dirname(__FILE__).'/'.$this->config['time_log'], $timestamp);


            // 画面表示
            echo "\n";
            echo '  -------------------------------------------------------'."\n";
            echo '            ***** 本田とじゃんけんBot '.$this->config['version'].' *****'."\n";
            echo '  -------------------------------------------------------'."\n\n";
            echo '    実行時刻　　　 : '.date('Y-m-d H:i:s', $timestamp)."\n";
            echo '    前回の実行時刻 : '.date('Y-m-d H:i:s', $timestamp_last)."\n";
            echo '    実行間隔　　　 : '.$interval.' 秒'."\n\n";
            echo '  -------------------------------------------------------'."\n\n";


            // アクセストークンがない場合
            if (!isset($this->config['OAUTH_TOKEN']) or !isset($this->config['OAUTH_TOKEN_SECRET'])){

                echo '    エラー: ツイートする場合はアクセストークンを設定してください。'."\n\n";
                echo '  -------------------------------------------------------'."\n\n";
                exit(1); // 終了

            }


            // Twitterに接続
            $this->connection = new TwitterOAuth($this->config['CONSUMER_KEY'], $this->config['CONSUMER_SECRET'], $this->config['OAUTH_TOKEN'], $this->config['OAUTH_TOKEN_SECRET']);

            // 毎時0分ならフォローバック
            if (date('i') == '00') {

                echo '    自動フォローバックを開始します。'."\n\n";

                // フォローバックを実行
                $this->followback();

                echo '    自動フォローバックを終了します。'."\n\n";
                echo '  -------------------------------------------------------'."\n\n";

            }

            // リプライを取得
            $tweets = $this->connection->get('statuses/mentions_timeline', array('count' => $this->config['tweet_acquisition'], 'tweet_mode' => 'extended'));

            echo '    直近 '.(count($tweets)).' 件のツイートを取得しました。'."\n\n";
            echo '    リプライへの返信を開始します。'."\n\n";
            echo '  -------------------------------------------------------'."\n\n";


            // ツイートを取り出す
            foreach ($tweets as $count => $tweet) {

                // ツイート投稿時刻のタイムスタンプ
                $timestamp_tweet = strtotime($tweet->created_at);

                echo '    ツイート '.($count + 1).':'."\n\n";
                echo '      ツイートの投稿日時: '.date('Y-m-d H:i:s', $timestamp_tweet)."\n";
                echo '      ツイートのタイムスタンプ: '.$timestamp_tweet."\n";
                echo '      ツイートのURL: https://twitter.com/'.$tweet->user->screen_name.'/status/'.$tweet->id."\n\n";

                // リプライが 自分 or pepsi_jpn からではない &
                // ツイート投稿時刻が前回の実行時刻よりも後
                if ($tweet->user->screen_name !== $this->config['screen_name'] and
                    $tweet->user->screen_name !== 'pepsi_jpn' and
                    $timestamp_tweet >= floor($timestamp_last)) { // 小数点以下は切り捨ててから比較する


                    // 勝率を設定
                    // #本田優しくして … 40%・#本田大好き … 70%
                    $this->setPercentage($tweet->full_text);

                    // 勝敗を決める
                    $random = rand(1, 100); // 1～100の乱数を取得

                    // 1～勝率の間なら
                    if ($random < $this->percentage){
                        $this->battle = self::YOU_WIN; // 勝った
                    } else {
                        $this->battle = self::YOU_LOSE; // 負けた
                    }

                    // ツイートする動画と文章を選択
                    $select = $this->selectTweet($tweet->full_text);

                    // 正常に選択できていれば
                    if (is_array($select)) {

                        // 結果を送信
                        $this->replyResultWithTweet($tweet->id, $tweet->user->screen_name, $select['tweet'], $select['video']);
                        echo '      結果をリプライしました。'."\n\n";

                    // ヘルプを送信
                    } else if ($select === '') {

                        // ヘルプを送信
                        $this->replyHelpWithTweet($tweet->id, $tweet->user->screen_name);
                        echo '      コマンドが指定されていないため、ヘルプを送信しました。'."\n\n";

                    // リプライをスキップ
                    } else {

                        echo '      コマンドが指定されていないため、リプライをスキップします。'."\n\n";

                    }


                } else if ($tweet->user->screen_name === $this->config['screen_name']) {

                    echo '      自分からのツイートのため、リプライをスキップします。'."\n\n";

                } else if ($tweet->user->screen_name === 'pepsi_jpn') {

                    echo '      Pepsi 公式からのツイートのため、リプライをスキップします。'."\n\n";

                } else if ($timestamp_tweet < $timestamp_last) {

                    echo '      前回の実行時刻よりも前にツイートされているため、リプライをスキップします。'."\n\n";

                }

            }

            echo '    リプライを終了します。'."\n\n";
            echo '    実行を完了しました。終了します。'."\n\n";
            echo '  -------------------------------------------------------'."\n\n";

        }


        /**
         * 勝利確率を設定する
         */
        public function setPercentage($text){

            // #本田大好き
            // 同時に指定されていたときはより勝率の高い方を設定するためにこの順番にしている
            if (strpos($text, '#本田大好き') !== false){

                // 勝率を 70% に設定
                $this->percentage = self::HONDA_LOVE;

            // #本田優しくして
            } else if (strpos($text, '#本田優しくして') !== false){

                // 勝率を 40% に設定
                $this->percentage = self::HONDA_KIND;

            } else {

                // デフォルト設定を使用
                $this->percentage = $this->config['percentage'];

            }

        }


        /**
         * フォローバックを行う
         */
        public function followback(){

            // フォロワーの ID を取得
            $followers = $this->connection->get('followers/ids', array('cursor' => -1));

            // フォロー中の ID を取得
            $friends = $this->connection->get('friends/ids', array('cursor' => -1));

            // フォロワーごとに処理
			foreach ($followers->ids as $follower) {

                // フォロー中のユーザーの中にフォロワーが含まれていなければ
				if (!in_array($follower, $friends->ids)) {

                    // ユーザー情報を取得
                    $profile = $this->connection->get('users/show', array('user_id' => $follower));

                    echo '    フォローバックするユーザーID: '.$profile->screen_name."\n";

                    // 鍵垢でなければ
                    if (!$profile->protected) {

                        // フォローバックする
                        $this->connection->post('friendships/create', array('user_id' => $follower));
                        echo "\n";

                    } else {

                        echo '    鍵垢のため、フォローバックは行いません。'."\n";
                        echo "\n";

                    }
                }
            }
        }


        /**
         * 投稿するツイートを選択して返す
         * @param boolean $text 判定するツイート本文
         * @return array array('video' => (動画のファイルパス), 'tweet' => (ツイート本文))
         */
        public function selectTweet($text){

            $assets = array(
                'Jyanken' => array(
                    'YOU_LOSE' => array(
                        'Text' => '残念、本田圭佑の勝利！'."\n\n".
                                  'YOU LOSE!!!! 俺の勝ち！😁'."\n".
                                  '%speech%'.
                                  'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n".
                                  '一日何回でも勝負。'."\n".
                                  'じゃあ、また今度👋☺️'."\n",
                        'Rock' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_lose_Rock.mp4',
                                'speech' => 'たかがじゃんけん、そう思ってないですか？'."\n".
                                            'それやったら明日も、俺が勝ちますよ。'."\n",
                            ),
                        ),
                        'Scissor' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_lose_Scissor.mp4',
                                'speech' => '何で負けたか明日までに考えといてください。そしたら何かが見えてくるはずです。'."\n",
                            ),
                        ),
                        'Paper' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_lose_Paper.mp4',
                                'speech' => '負けは次に繋がるチャンスです。ネバーギブアップ！'."\n",
                            ),
                        ),
                    ),
                    'YOU_WIN' => array(
                        'Text' => 'お見事！あなたの勝利！'."\n\n".
                                  'YOU WIN!!!! やるやん。'."\n".
                                  '明日は俺にリベンジさせて。'."\n".
                                  'では、どうぞ👋☺️(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n",
                        'Rock' => array(
                            'video' => dirname(__FILE__).'/video/honda_win_Rock.mp4',
                        ),
                        'Scissor' => array(
                            'video' => dirname(__FILE__).'/video/honda_win_Scissor.mp4',
                        ),
                        'Paper' => array(
                            'video' => dirname(__FILE__).'/video/honda_win_Paper.mp4',
                        ),
                    ),
                ),
                'CardBattle' => array(
                    'YOU_LOSE' => array(
                        'Text' => '残念、本田圭佑の勝利！'."\n\n".
                                  'YOU LOSE!!!! 俺の勝ち！😁'."\n".
                                  '%speech%'.
                                  'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n".
                                  '(ｯﾊｰ)うまい！1日何回でも勝負。'."\n".
								  'また今度☺️'."\n",
                        'CardA' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_card_lose_A1.mp4',
                                'speech' => 'カードの向こう側に何があるか、考えてみてください。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_card_lose_A2.mp4',
                                'speech' => 'ケイスケ ホンダの心なんて読めるわけない、そう思ってないですか？'."\n".
                                            'あきらめへん人だけに見える景色があるはずです。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_card_lose_A3.mp4',
                                'speech' => 'ウラのウラのウラまで、読む訓練をしてくださいね。'."\n".
                                            'どこまで読もうとするかで、結果が変わってきます。'."\n",
                            ),
                        ),
                        'CardB' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_card_lose_B1.mp4',
                                'speech' => 'どんな事でも、絶対に勝つんや！というメンタリティーが大事ですよ。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_card_lose_B2.mp4',
                                'speech' => '自信を持って、勝負にしっかりと向き合える。'."\n".
                                            'そう思えるまで、準備してください。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_card_lose_B3.mp4',
                                'speech' => '複雑に考えてないですか？答えはシンプルです。'."\n".
                                            'ケイスケ ホンダの心を読む、それだけです。'."\n",
                            ),
                        ),
                    ),
                    'YOU_WIN' => array(
                        'Text' => 'お見事！あなたの勝利！'."\n\n".
                                  'YOU WIN!!!! 俺の負け！'."\n".
                                  'やるやん！'."\n".
                                  'でも、今度は絶対、俺が勝つから！'."\n".
                                  'また明日やろう☺️(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n",
                        'CardA' => array(
                            'video' => dirname(__FILE__).'/video/honda_card_win_A.mp4',
                        ),
                        'CardB' => array(
                            'video' => dirname(__FILE__).'/video/honda_card_win_B.mp4',
                        ),
                    ),
                ),
                'CoinToss' => array(
                    'YOU_LOSE' => array(
                        'Text' => '残念、本田圭佑の勝利！'."\n\n".
                                  'YOU LOSE!!!! 俺の勝ち！😁'."\n".
                                  '%speech%'.
                                  'ほな、いただきます(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n\n".
                                  '(ｯﾊｰ)うまい！1日何回でも勝負。'."\n".
								  'また今度☺️'."\n",
                        'CoinH' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_coin_lose_H1.mp4',
                                'speech' => 'いい勝負でしたね！でも、結果が伴わないと全く意味がありません。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_coin_lose_H2.mp4',
                                'speech' => 'ちゃんと分析してます？じっくり結果に向き合ってください。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_coin_lose_H3.mp4',
                                'speech' => '運を味方につけるのは、地道な努力ですよ。'."\n",
                            ),
                        ),
                        'CoinK' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_coin_lose_K1.mp4',
                                'speech' => 'ただの運やと思ってませんか？'."\n".
                                            '運も実力のうち！聞いたことありますよね？'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_coin_lose_K2.mp4',
                                'speech' => '正確には、コインを味方につけた俺の勝ち！'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_coin_lose_K3.mp4',
                                'speech' => '動揺してませんか？'."\n".
                                            '運が大事な時こそ、集中力が物を言いますよ！'."\n",
                            ),
                        ),
                    ),
                    'YOU_WIN' => array(
                        'Text' => 'お見事！あなたの勝利！'."\n\n".
                                  'YOU WIN!!!! 俺の負け！(ﾁｯ)(ｱｱｰ…)'."\n".
                                  '今度俺が勝つから、またやろう☺️(ﾌﾟｼｭｳｳｳｳｳｳｳ)'."\n",
                        'CoinH' => array(
                            'video' => dirname(__FILE__).'/video/honda_coin_win_H.mp4',
                        ),
                        'CoinK' => array(
                            'video' => dirname(__FILE__).'/video/honda_coin_win_K.mp4',
                        ),
                    ),
                ),
                'Jyanken2020' => array(
                    'YOU_LOSE' => array(
                        'Text' => '残念、本田圭佑の勝利！'."\n\n".
                                  'YOU LOSE!!!! 俺の勝ち！😁'."\n".
                                  '%speech%'.
                                  'ほな、注ぎます (ﾌﾟｼｭｳｳｳｳｳｳ……)'."\n\n".
                                  '(ｯﾊｰ) …めっちゃ美味い！'."\n".
                                  '飲みたい？ ほな、今度も挑戦☺️(ﾌﾟｼｭｳｳｳｳｳｳ)'."\n",
                        'Rock' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_2020_lose_Rock1.mp4',
                                'speech' => 'ここは練習ではありません。'."\n".
                                            '全身全霊で、俺と向き合ってください。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_2020_lose_Rock2.mp4',
                                'speech' => '何事も、準備がすべて。'."\n".
                                            'それを怠っている事がバレてますよ。'."\n",
                            ),
                        ),
                        'Scissor' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_2020_lose_Scissor1.mp4',
                                'speech' => 'あなたの考えてる事くらい、俺にはお見通しです。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_2020_lose_Scissor2.mp4',
                                'speech' => 'その程度の、気持ちで勝てるとでも思ったんですか？'."\n".
                                            'ちゃんと練習してきてください。'."\n",
                            ),
                        ),
                        'Paper' => array(
                            array(
                                'video' => dirname(__FILE__).'/video/honda_2020_lose_Paper1.mp4',
                                'speech' => '1年間何やってたんですか？'."\n".
                                            'この結果は、じゃんけんに対する、意識の差です。'."\n",
                            ),
                            array(
                                'video' => dirname(__FILE__).'/video/honda_2020_lose_Paper2.mp4',
                                'speech' => 'それで勝てると思ってるんやったら、俺がずっと勝ちますよ！'."\n",
                            ),
                        ),
                    ),
                    'YOU_WIN' => array(
                        'Text' => 'お見事！あなたの勝利！'."\n\n".
                                  'YOU WIN!!!! 俺の負け！'."\n".
                                  '今日は負けを認めます。'."\n".
                                  'ただ、勝ち逃げは、許しませんよ。'."\n".
                                  'ほな、注ぎます (ﾌﾟｼｭｳｳｳｳｳｳ……)'."\n\n".
                                  '今度もここで待ってますから、では、どうぞ☺️(ﾌﾟｼｭｳｳｳｳｳｳ)'."\n",
                        'Rock' => array(
                            'video' => dirname(__FILE__).'/video/honda_2020_win_Rock.mp4',
                        ),
                        'Scissor' => array(
                            'video' => dirname(__FILE__).'/video/honda_2020_win_Scissor.mp4',
                        ),
                        'Paper' => array(
                            'video' => dirname(__FILE__).'/video/honda_2020_win_Paper.mp4',
                        ),
                    ),
                ),
            );


            // 本田とじゃんけん & 本田とじゃんけん2020
            if (strpos($text, '#本田とじゃんけん') !== false or
                strpos($text, '#本田にグーで勝つ') !== false or
                strpos($text, '#本田にチョキで勝つ') !== false or
                strpos($text, '#本田にパーで勝つ') !== false) {

                // 本田とじゃんけん(2019)
                if (strpos($text, '#本田とじゃんけん2020') === false and // #本田とじゃんけん 2020 が含まれていなくて
                    strpos($text, '#本田とじゃんけん') !== false) { // #本田とじゃんけんが含まれていたら

                    // 種類
                    $battle_type = 'Jyanken';

                // 本田とじゃんけん2020
                } else {

                    // 種類
                    $battle_type = 'Jyanken2020';

                }

                // #本田にグーで勝つ
                // あいこは存在しない
                if (strpos($text, '#本田にグーで勝つ') !== false) {

                    $command = '#本田にグーで勝つ'; // コマンド

                    // YOU LOSE
                    if ($this->battle === self::YOU_LOSE) {

                        $result = 'Paper'; // 本田の選択: パー

                    // YOU WIN
                    } else if ($this->battle === self::YOU_WIN) {

                        $result = 'Scissor'; // 本田の選択: チョキ

                    }

                // 本田にチョキで勝つ
                } else if (strpos($text, '#本田にチョキで勝つ') !== false) {

                    $command = '#本田にチョキで勝つ'; // コマンド

                    // YOU LOSE
                    if ($this->battle === self::YOU_LOSE) {

                        $result = 'Rock'; // 本田の選択: グー

                    // YOU WIN
                    } else if ($this->battle === self::YOU_WIN) {

                        $result = 'Paper'; // 本田の選択: パー

                    }

                // 本田にパーで勝つ
                } else if (strpos($text, '#本田にパーで勝つ') !== false) {

                    $command = '#本田にパーで勝つ'; // コマンド

                    // YOU LOSE
                    if ($this->battle === self::YOU_LOSE) {

                        $result = 'Scissor'; // 本田の選択: チョキ

                    // YOU WIN
                    } else if ($this->battle === self::YOU_WIN) {

                        $result = 'Rock'; // 本田の選択: グー

                    }
                }

            // 本田とカードバトル
            } else if (strpos($text, '#本田とカードバトル') !== false or
                       strpos($text, '#私は本田のAを引く') !== false or
                       strpos($text, '#私は本田のBを引く') !== false) {

                // 種類
                $battle_type = 'CardBattle';

                // #私は本田のAを引く
                // 勝ち負けに関わらずAならAのカードの裏を、BならBのカードの裏をそのまま表示する（こんがらがりポイント）
                if (strpos($text, '#私は本田のAを引く') !== false) {

                    $command = '#私は本田のAを引く'; // コマンド

                    $result = 'CardA'; // 選択: Aのカード

                // #私は本田のBを引く
                } else if (strpos($text, '#私は本田のBを引く') !== false) {

                    $command = '#私は本田のBを引く'; // コマンド

                    $result = 'CardB'; // 選択: Bのカード

                }

            // 本田とコイントス
            } else if (strpos($text, '#本田とコイントス') !== false or
                       strpos($text, '#私はHのコインを選ぶ') !== false or
                       strpos($text, '#私はKのコインを選ぶ') !== false) {

                // 種類
                $battle_type = 'CoinToss';

                // #私はHのコインを選ぶ
                if (strpos($text, '#私はHのコインを選ぶ') !== false) {

                    $command = '#私はHのコインを選ぶ'; // コマンド

                    // YOU LOSE
                    if ($this->battle === self::YOU_LOSE) {

                        $result = 'CoinK'; // 選択: Kのコイン (不一致)

                    // YOU WIN
                    } else if ($this->battle === self::YOU_WIN) {

                        $result = 'CoinH'; // 選択: Hのコイン (一致)

                    }

                // #私はKのコインを選ぶ
                } else if (strpos($text, '#私はKのコインを選ぶ') !== false) {

                    $command = '#私はKのコインを選ぶ'; // コマンド

                    // YOU LOSE
                    if ($this->battle === self::YOU_LOSE) {

                        $result = 'CoinH'; // 選択: Hのコイン (不一致)

                    // YOU WIN
                    } else if ($this->battle === self::YOU_WIN) {

                        $result = 'CoinK'; // 選択: Kのコイン (一致)

                    }
                }
            }

            // どれにも当てはまらなかったらヘルプを送信する
            if (!isset($result)) {

                // その他のリプライ全てに返信すると過剰なので「#」と「本田」が含まれる &「俺の勝ち」と「俺の負け」が含まれない場合に限る
                if (strpos($text, '#') !== false and
                        strpos($text, '本田') !== false and
                        strpos($text, '俺の勝ち') === false and
                        strpos($text, '俺の負け') === false) {

                    return ''; // 空

                // それ以外は null を返す
                } else {

                    return null; // 空

                }
            }


            // YOU LOSE
            if ($this->battle === self::YOU_LOSE) {

                // 動画を用意されているものからランダムで選ぶ
                $random = rand(0, (count($assets[$battle_type]['YOU_LOSE'][$result]) - 1));

                // 動画
                $tweet_video = $assets[$battle_type]['YOU_LOSE'][$result][$random]['video'];

                // ツイート本文（ %speech% を置換する）
                $tweet_text = str_replace('%speech%',
                    $assets[$battle_type]['YOU_LOSE'][$result][$random]['speech'],
                    $assets[$battle_type]['YOU_LOSE']['Text']
                );

                // #本田優しくして or #本田大好き が指定されていた場合
                if ($this->percentage === self::HONDA_KIND or $this->percentage === self::HONDA_LOVE) {

                    $tweet_text = str_replace('YOU LOSE!!!!', 'まだまだ甘いです。YOU LOSE!!!!', $tweet_text);

                }

				echo '      '.$command.' (勝率: '.$this->percentage.'%): 残念、本田圭佑の勝利！'."\n\n";

            } else if ($this->battle === self::YOU_WIN) {

                // 動画
                $tweet_video = $assets[$battle_type]['YOU_WIN'][$result]['video'];

                // ツイート本文
                $tweet_text = $assets[$battle_type]['YOU_WIN']['Text'];

                // #本田優しくして or #本田大好き が指定されていた場合
                if ($this->percentage === self::HONDA_KIND or $this->percentage === self::HONDA_LOVE) {

                    $tweet_text = str_replace('YOU WIN!!!!', '勝ててよかったな。YOU WIN!!!!', $tweet_text);

                }

				echo '      '.$command.' (勝率: '.$this->percentage.'%): お見事！あなたの勝利！'."\n\n";

            }

            // 動画とツイートを返す
            return array(
                'video' => $tweet_video,
                'tweet' => $tweet_text,
            );

        }


        /**
         * 指定されたIDのツイートにリプライを送信する
         *
         * @param int $tweet_id リプライを行うツイートのID
         * @param string $screen_name スクリーンネーム (ID)
         * @param string $text ツイート本文
         * @param object $upload アップロードした結果のオブジェクト (オプション)
         * @return int 送信したリプライツイートのID
         */
        public function reply($tweet_id, $screen_name, $text, $upload = null){

            // 送信するパラメータを準備
            $send['status'] = '@'.$screen_name."\n".$text;
            $send['in_reply_to_status_id'] = $tweet_id;
            if ($upload !== null) $send['media_ids'] = implode(',', [$upload->media_id_string]);

            // リプライを送信
            $result = $this->connection->post('statuses/update', $send);

            if (isset($result->errors)) {
                echo '      送信時にエラーが発生しました (code: '.$result->errors[0]->code.'): '.$result->errors[0]->message."\n";
                echo '      文字数: '.mb_strlen($text).'字'."\n";
                return false; // エラー
            } else {
                return $result->id;
            }

        }


        /**
         * 指定されたIDのツイートに勝負の結果を送信する
         *
         * @param int $tweet_id リプライを行うツイートのID
         * @param string $screen_name スクリーンネーム (ID)
         * @param string $text ツイート本文
         * @param string $video 動画のファイルパス
         */
        public function replyResultWithTweet($tweet_id, $screen_name, $text, $video){

            // 動画をアップロード
            $upload = $this->connection->upload('media/upload', array('media' => $video, 'media_type' => 'video/mp4', 'media_category' => 'tweet_video'), true);

            // 動画が使えるようになるまで数秒待つ
            // これをやらないと (code: 324): Not valid video が発生する
            sleep(3);

            // リプライするツイートをファボる
            $this->connection->post('favorites/create', array('id' => $tweet_id));

            // リプライを送信
            $this->reply($tweet_id, $screen_name, $text, $upload);

        }


        /**
         * 指定されたIDのツイートにヘルプを送信する
         *
         * @param int $tweet_id リプライを行うツイートのID
         * @param string $screen_name スクリーンネーム (ID)
         */
        public function replyHelpWithTweet($tweet_id, $screen_name){

            // ヘルプの内容（配列）
            $helps = array(

                'ヘルプを送信しときます。'."\n".
                '#本田とじゃんけん / #本田とじゃんけん2020 / #本田とカードバトル / #本田とコイントス を再現する非公式 Bot です。'."\n\n".
                'それぞれ、「@HondaJyanken #本田とじゃんけん #本田に(グー or チョキ or パー)で勝つ」、(続く)'."\n",

                '「@HondaJyanken #本田とじゃんけん2020 #本田に(グー or チョキ or パー)で勝つ」、'."\n".
                '「@HondaJyanken #本田とカードバトル #私は本田の(A or B)を引く」、'."\n".
                '「@HondaJyanken #本田とコイントス #私は(H or K)のコインを選ぶ」とツイートしとくと結果が (続く)'."\n",

                '返ってくるはずです。'."\n".
                '勝率は '.$this->config['percentage'].'% ですが、ツイートに #本田優しくして とつけると勝率が '.self::HONDA_KIND.'% に、#本田大好き とつけると勝率が '.self::HONDA_LOVE.'% に上がります。'."\n".
                'どうしても勝てない時、試しといてください。'."\n\n".
                '1日何回でも勝負できます。'."\n".
                'ほな、(勝利) いただきます😁'."\n",

            );

            foreach ($helps as $help) {

                // ツイートを送信
                $tweet_id = $this->reply($tweet_id, $screen_name, $help); // 同時にリプライするツイートのIDを上書き

            }
        }

    }

