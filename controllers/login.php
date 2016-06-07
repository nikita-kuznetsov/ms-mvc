<?php
Class Controller_Login Extends Controller_Base
{	
	public function index()
	{
		$this->drop_old_ses();
	}
	
	public function getin()
	{
		$this->drop_old_ses();
		
		$is_exists_login = false;		
		
		$db = $this->registry['db'];
		$logger = $this->registry['logger'];		
		
		$login = $db->Q->real_escape_string($_POST['login']);
		
		if ($login!='')
		{
			$sql = "SELECT f_uid, f_login FROM t_ib_users WHERE f_login = '{$login}' AND f_uid != ''";
			$result = $db->Q->query($sql);
			$arr = $result->fetch_assoc();

			$f_uid = $arr['f_uid'];
			$f_login = $arr['f_login'];			

			if ($f_uid!='' and $f_login!='')
				$is_exists_login = true;
		}
		else
			$is_exists_login = false;
		
		if ($is_exists_login)		
		{	
			$otp = $this->ab_gen_otp();	
			$otp = "0000";
			
			$uid = $f_uid;			
			$ptim = $this->ab_rts(36);
			$wrap = hash('sha512', $otp.$ptim);
			
			$tmp_f_key = hash("sha512", $uid.date('Y-m-d'));
			$f_tim = date('Y-m-d H:i:s');
			
			$sql = "DELETE FROM t_ses WHERE f_key = '{$tmp_f_key}'";
			$db->Q->query($sql);
			
			$sql = "INSERT t_ses SET f_key = '{$tmp_f_key}', f_tim = '{$f_tim}', f_str = '{$wrap}'";
			$db->Q->query($sql);
			
			echo json_encode(array('result' => 1, 'message' => '', 'wrap' => $wrap, 'ptim' => $ptim, 'uid' => $uid, 'recap' => 0));		
		}
		else		
			echo json_encode(array('result' => 0, 'message' => 'ib.notUser', 'wrap' => '', 'ptim' => '', 'uid' => 0, 'recap' => 1));		
	}
	
	public function set_abs_key()
	{
		$this->drop_old_ses();
		
		$auth = $this->registry['auth'];
		$db = $this->registry['db'];
		$logger = $this->registry['logger'];
		
		$uid = $db->Q->real_escape_string($_POST['uid']);
		$wrap = $db->Q->real_escape_string($_POST['wrap']);
		
		$absKey = hash("sha512", $uid.$wrap);
		
		$tmp_f_key = hash("sha512", $uid.date('Y-m-d'));		
		
		$sql = "SELECT f_key FROM t_ses WHERE f_key = '{$tmp_f_key}' AND f_str = '{$wrap}'";
		
		$rc = $db->Q->query($sql);
		$arr = $db->fetch_assoc($rc);
		
		$f_key = $arr['f_key'];
		
		if ($f_key!='')
		{
			$f_key = $auth->user_hash($uid);
			$f_tim = date('Y-m-d H:i:s');
			
			$sql = "DELETE FROM t_ses WHERE f_key = '{$tmp_f_key}'";
			$db->Q->query($sql);
			
			$sql = "INSERT INTO t_ses SET f_key = '{$f_key}', f_tim = '{$f_tim}', f_str = '{$absKey}' ON DUPLICATE KEY UPDATE f_key = '{$f_key}', f_tim = '{$f_tim}', f_str = '{$absKey}'";
			$db->Q->query($sql);
			
			$result = array("absKey" => $absKey);
			echo json_encode($result);
			
			$logger->write($uid, "000");
		}			
	}
	
	public function check_abs_key()
	{
		$this->drop_old_ses();
		
		$auth = $this->registry['auth'];
		$db = $this->registry['db'];
		$logger = $this->registry['logger'];
		
		$uid = $db->Q->real_escape_string($_POST['uid']);
		$key = $db->Q->real_escape_string($_POST['key']);		
		$f_key = $auth->user_hash($uid);		
		
		$check_session = $auth->check_session();

		if ($check_session)
		{
			$r = 1;			
			$f_tim = date('Y-m-d H:i:s');			
			$sql = "UPDATE t_ses SET f_tim = '{$f_tim}' WHERE f_key = '{$f_key}' AND f_str = '{$key}'";
			$db->Q->query($sql);
		}			
		else
		{
			$r = 0;
			$logger->write($uid, "002");
		}			
		
		$result = array("result" => $r);
		echo json_encode($result);
	}
	
	public function out()
	{
		$this->drop_old_ses();
		
		$auth = $this->registry['auth'];
		$db = $this->registry['db'];
		$logger = $this->registry['logger'];		
		
		$uid = $db->Q->real_escape_string($_POST['uid']);
		$key = $db->Q->real_escape_string($_POST['key']);		
		
		$f_key = $auth->user_hash($uid);
		
		if ($f_key!='' and $key!='')
		{
			$sql = "DELETE FROM t_ses WHERE f_key = '{$f_key}' AND f_str = '{$key}'";
			$db->Q->query($sql);
			
			$logger->write($uid, "003");
		}
	}
	
	private function ab_rts($ls)
	{
		$t = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
		$f = '';
		for ($i = 0; $i < $ls; $i++) {$f .= $t[rand(0, strlen($t) - 1)];}
		
		$key = $f.'_'.time();		
		return $key;
	}
	
	private function ab_gen_otp($len=4)
	{
		$otp = "";
		for($i=0;$i<$len;$i++)		
			$otp.= rand(0, 9);
		
		return $otp;
	}	
	
	private function drop_old_ses()
	{
		global $ABSES;
		
		$db = $this->registry['db'];		
		
		$lim = date('Y-m-d H:i:s', time() - $ABSES);
		$sql = "DELETE FROM t_ses WHERE f_tim < '$lim'";
		$db->Q->query($sql);
	}
}
?>