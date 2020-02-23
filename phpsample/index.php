<?php
	require_once '/var/saga/data.php';

	session_start();

	$visiterIp = $_SERVER["REMOTE_ADDR"];

	$authNo = htmlspecialchars($_POST["authNo"]);

	$message = "";

	$resultMAuth = null;


	if(!preg_match("/^[0-9]{6}$/", $authNo)){
		$_SESSION['message'] = "入力された値が不正です。数値6桁で入力してください。";
		header('Location: ./error.php');
	} else {
		try {

			$dao = new Data();

			$result = $dao->getVDlStatus($authNo,$visiterIp);
			$reqCnt = count($result);
			if($reqCnt == 0){
				$resultMAuth = $dao->getMAuth($authNo, 1);
				if(count($resultMAuth) == 0) {
					$message = "DLシリアルNoが誤っています";
					throw new ErrorException();
				}
				$resultHistory = $dao->getTDlHistory($resultMAuth[0]['auth_id']);
				if(count($resultHistory) != 0){
					$message = "1端末からしかDLできません。他端末でDL済のようです。";
					throw new ErrorException();
				}
			} else {
				$resultMAuth = $dao->getMAuth($authNo, 1);
				if(count($resultMAuth) == 0) {
					$message = "DLシリアルNoが誤っています";
					throw new ErrorException("");
				}
				if($result[0]['dl_count'] > 2){
					$message = "DL回数を超過しています";
					throw new ErrorException("");
				}
			}
			$insertResult = $dao->excuteInsertHistory($visiterIp, $resultMAuth[0]["auth_id"], 1);
			if(!$insertResult){
				$message = "処理に失敗しました";
				throw new ErrorException();
			}
			$filename = "/tmp/omake/omake.zip";
			header('Content-Type: application/octet-stream');
			header('Content-Length: ' . filesize($filename));
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			readfile($filename);
		} catch ( Exception $e ){
			$dbh = null;
			$_SESSION['message'] = $message;
			header('Location: ./error.php');
		}
		$dbh = null;

	}

