<?php

// 設定読み込み
require (dirname(__FILE__).'/config.php');

?>
<!Doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <title>本田とじゃんけんBot <?php echo $version; ?></title>
</head>
<body>
  <h2>本田とじゃんけんBot <?php echo $version; ?></h2>
  <a href="tweet.php">Botを実行する</a><br>
  <a href="tweet_auto.php">自動ツイートを投稿する</a><br>
<?php

	// ログがあれば
	if (file_exists($logfile)){

?>
  <h2>直近の実行ログ</h2>
<?php

		// ログ表示
		$log = explode("\n", file_get_contents($logfile));
		for ($i = 0; $i < count($log); $i++){
			echo $log[$i]."<br>\n";
		}
	}
?>
<body>
<html>