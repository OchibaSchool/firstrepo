<?php
/**
 * DBへのデータアクセスを定義するクラス
 */
class Data {
	/* クラス内変数 */
	// DB接続情報
	private $dsn = 'pgsql:dbname=saga_dl host=localhost port=5432';
	// DB接続ユーザー
	private $user = 'saga';
	// DB接続パスワード
	private $password = 'xxxxxxx';
	// DB接続ハンドラー
	private $dbh = null;

	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		// DB接続を実行する
		$this->dbh = new PDO($this->dsn, $this->user, $this->password);
	}

	/**
	 * ダウンロード状況ビューを取得する
	 * @param $authNo 認証キー
	 * @param $ip 接続元IP
	 */
	public function getVDlStatus($authNo,$ip){
		$stmt = $this->dbh->prepare($this->vDlStatusSql());
		$stmt->bindValue(1, $authNo,PDO::PARAM_STR);
		$stmt->bindValue(2, $ip,PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetchAll();
		return $result;
	}

	/**
	 * 認証マスタを取得する
	 * @param $authNo 認証キー
	 * @param $productionId コンテンツID
	 */
	public function getMAuth($authNo,$productionId){
		$stmt = $this->dbh->prepare($this->mAuthSql());
		$stmt->bindValue(1, $authNo,PDO::PARAM_STR);
		$stmt->bindValue(2, $productionId,PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll();
		return $result;
	}

	/**
	 * ダウンロード履歴テーブルを取得する
	 * @param $authNo 認証キー
	 */
	public function getTDlHistory($authNoId){
		$stmt = $this->dbh->prepare($this->tDlHistory());
		$stmt->bindValue(1, $authNoId,PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll();
		return $result;
	}

	/**
	 * ダウンロード履歴テーブルにデータを登録する
	 * @param $ip 接続元IP
	 * @param $authNo 認証キー
	 * @param $productionId コンテンツID
	 */
	public function excuteInsertHistory($ip,$authId,$productionId){
		$insertStmt = $this->dbh->prepare($this->insertHistoryTableSql());
		$insertStmt->bindValue(1, $authId,PDO::PARAM_STR);
		$insertStmt->bindValue(2, $productionId,PDO::PARAM_INT);
		$insertStmt->bindValue(3, $ip,PDO::PARAM_STR);
		$result = $insertStmt->execute();
		return $result;
	}

	/**
	 * ダウンロード状況ビューの取得SQLを発行する
	 */
	private function vDlStatusSql(){
		$sql = 'select * from V_DL_STATUS where auth_no = ? and dl_ip = ?';
		return $sql;
	}

	/**
	 * 認証マスタの取得SQLを発行する
	 */
	private function mAuthSql(){
		$sql = 'select * from m_dl_auth where auth_no = ? and auth_production_id = ?';
		return $sql;
	}

	/**
	 * ダウンロード履歴テーブルの登録SQLを発行する
	 */
	private function insertHistoryTableSql(){
		$insertSQL = 'insert into T_DL_HISTORY '
		.'(HISTORY_ID'
		.', HISTORY_AUTH_ID'
		.', HISTORY_PRODUCTION_ID'
		.', DL_DATE'
		.', DL_IP'
		.', INSERT_DATE'
		.', UPDATE_DATE'
		.', DELETE_FLG) values ('
		." nextval('t_dl_history_history_id_seq')"
		.', ?'
		.', ?'
		.', CURRENT_TIMESTAMP'
		.', ?'
		.', CURRENT_TIMESTAMP'
		.', CURRENT_TIMESTAMP'
		.", '0')";
		return $insertSQL;
	}

	/**
	 * ダウンロード履歴テーブルの取得SQLを発行する
	 */
	private function tDlHistory(){
		$sql = "select * from t_dl_history where delete_flg = '0' and history_auth_id = ?";
		return $sql;
	}
}