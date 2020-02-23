<?php
	// データクラスのphpファイルを読み込み
	require_once '/var/saga/data.php';
	// セッション開始
	session_start();
	// アクセスユーザーのIPアドレスを取得
	$visiterIp = $_SERVER["REMOTE_ADDR"];
	// 入力された認証キーを取得
	$authNo = htmlspecialchars($_POST["authNo"]);
	// エラーページに返すメッセージを保持する変数を宣言
	$message = "";
	// 認証結果
	$resultMAuth = null;

	// 認証キーのフォーマットチェック
	// フォーマットが不正の場合、エラーページへ飛ばす
	if(!preg_match("/^[0-9]{6}$/", $authNo)){
		// エラーページへ渡すセッション情報を直接代入
		$_SESSION['message'] = "入力された値が不正です。数値6桁で入力してください。";
		// エラーページへ飛ばす
		header('Location: ./error.php');
	// フォーマットが正常の場合
	} else {
		// DB接続をtry
		try {

			// Dataオブジェクトのインスタンスを作成
			$dao = new Data();

			// ダウンロード状況を検索
			$result = $dao->getVDlStatus($authNo,$visiterIp);
			// 履歴件数を取得
			$reqCnt = count($result);
			
			// コンテンツダウンロードの資格チェックここから
			
			// 履歴件数が0 (= 初回ダウンロード)
			if($reqCnt == 0){
				// 認証キーを検索
				$resultMAuth = $dao->getMAuth($authNo, 1);
				// 認証キーが存在しない場合、エラーとして例外を出す
				if(count($resultMAuth) == 0) {
					// メッセージを変数に格納
					$message = "DLシリアルNoが誤っています";
					// 例外を飛ばす
					throw new ErrorException();
				}
				// 認証キーが存在した場合
				// ダウンロード履歴を取得
				$resultHistory = $dao->getTDlHistory($resultMAuth[0]['auth_id']);
				// ダウンロード履歴が1件以上(= 他端末からダウンロードしている)
				if(count($resultHistory) != 0){
					// メッセージを変数に格納
					$message = "1端末からしかDLできません。他端末でDL済のようです。";
					// 例外を飛ばす
					throw new ErrorException();
				}
			// 履歴件数が1以上 (= 2回目以降のダウンロード)
			} else {
				// 認証キーを検索
				$resultMAuth = $dao->getMAuth($authNo, 1);
				// 認証キーが存在しない場合、エラーとして例外を出す
				if(count($resultMAuth) == 0) {
					// メッセージを変数に格納
					$message = "DLシリアルNoが誤っています";
					// 例外を飛ばす
					throw new ErrorException("");
				}
				// ダウンロード回数が3回以上
				if($result[0]['dl_count'] > 2){
					// メッセージを変数に格納
					$message = "DL回数を超過しています";
					// 例外を飛ばす
					throw new ErrorException("");
				}
			}
			
			// コンテンツダウンロードの資格チェックここまで
			
			// ダウンロード履歴を登録
			$insertResult = $dao->excuteInsertHistory($visiterIp, $resultMAuth[0]["auth_id"], 1);
			// 登録実行結果がfalse
			if(!$insertResult){
				// メッセージを変数に格納
				$message = "処理に失敗しました";
				// 例外を飛ばす
				throw new ErrorException();
			}
			// コンテンツダウンロード処理を実行
			// コンテンツのパスを設定
			$filename = "/tmp/omake/omake.zip";
			// ブラウザにヘッダー情報送信
			// コンテンツタイプを指定
			header('Content-Type: application/octet-stream');
			// ファイルサイズを指定
			header('Content-Length: ' . filesize($filename));
			// コンテンツ名を設定
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			// ファイルを読み出し(headerでブラウザへの送信処理が始まっているので
			// ファイルをreadするだけでファイルの中身がブラウザへ送信される
			readfile($filename);
		// tryの中で発生したエラーの処理
		} catch ( Exception $e ){
			// db接続を切断
			$dbh = null;
			// セッションにメッセージを格納
			$_SESSION['message'] = $message;
			// エラーページへ飛ばす
			header('Location: ./error.php');
		}
		// db接続を切断
		$dbh = null;

	}

