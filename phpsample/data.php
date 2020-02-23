<?php
/**
 * DB�ւ̃f�[�^�A�N�Z�X���`����N���X
 */
class Data {
	/* �N���X���ϐ� */
	// DB�ڑ����
	private $dsn = 'pgsql:dbname=saga_dl host=localhost port=5432';
	// DB�ڑ����[�U�[
	private $user = 'saga';
	// DB�ڑ��p�X���[�h
	private $password = 'xxxxxxx';
	// DB�ڑ��n���h���[
	private $dbh = null;

	/**
	 * �R���X�g���N�^
	 */
	public function __construct()
	{
		// DB�ڑ������s����
		$this->dbh = new PDO($this->dsn, $this->user, $this->password);
	}

	/**
	 * �_�E�����[�h�󋵃r���[���擾����
	 * @param $authNo �F�؃L�[
	 * @param $ip �ڑ���IP
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
	 * �F�؃}�X�^���擾����
	 * @param $authNo �F�؃L�[
	 * @param $productionId �R���e���cID
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
	 * �_�E�����[�h�����e�[�u�����擾����
	 * @param $authNo �F�؃L�[
	 */
	public function getTDlHistory($authNoId){
		$stmt = $this->dbh->prepare($this->tDlHistory());
		$stmt->bindValue(1, $authNoId,PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll();
		return $result;
	}

	/**
	 * �_�E�����[�h�����e�[�u���Ƀf�[�^��o�^����
	 * @param $ip �ڑ���IP
	 * @param $authNo �F�؃L�[
	 * @param $productionId �R���e���cID
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
	 * �_�E�����[�h�󋵃r���[�̎擾SQL�𔭍s����
	 */
	private function vDlStatusSql(){
		$sql = 'select * from V_DL_STATUS where auth_no = ? and dl_ip = ?';
		return $sql;
	}

	/**
	 * �F�؃}�X�^�̎擾SQL�𔭍s����
	 */
	private function mAuthSql(){
		$sql = 'select * from m_dl_auth where auth_no = ? and auth_production_id = ?';
		return $sql;
	}

	/**
	 * �_�E�����[�h�����e�[�u���̓o�^SQL�𔭍s����
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
	 * �_�E�����[�h�����e�[�u���̎擾SQL�𔭍s����
	 */
	private function tDlHistory(){
		$sql = "select * from t_dl_history where delete_flg = '0' and history_auth_id = ?";
		return $sql;
	}
}