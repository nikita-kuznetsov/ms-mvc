 <?php
Class Logger
{
	private $registry;	
	
	function __construct($registry)
	{
		$this->registry = $registry;
	}
	
	function write($uid, $action)
	{
		$this->setUid($uid);
		$this->init();
		
		$db = $this->registry['db'];
		
		$info['server'] = json_decode($_POST['log_server_info']);		
		$info['cookie'] = json_decode($_POST['log_cookie_info']);		
		
		$REMOTE_ADDR = $info['server']->REMOTE_ADDR;
		$HTTP_X_REAL_IP = $info['server']->HTTP_X_REAL_IP;
		$HTTP_X_FORWARDED_FOR = $info['server']->HTTP_X_FORWARDED_FOR;
		
		
		$f_tim = date("d.m.Y H:i:s");
		
		if ($HTTP_X_FORWARDED_FOR!='')
			$f_ip = $HTTP_X_FORWARDED_FOR;
		elseif ($HTTP_X_REAL_IP!='')
			$f_ip = $HTTP_X_REAL_IP;
		else
			$f_ip = $REMOTE_ADDR;		
		
		$f_browser = $info['server']->HTTP_USER_AGENT;
		$f_action_type = $action;
		$f_info = json_encode($info);		
		
		if ($this->table!='')
		{
			$f_tim = $db->Q->real_escape_string($f_tim);
			$f_ip = $db->Q->real_escape_string($f_ip);
			$f_browser = $db->Q->real_escape_string($f_browser);
			$f_action_type = $db->Q->real_escape_string($f_action_type);
			$f_info = $db->Q->real_escape_string($f_info);
			
			$sql = "INSERT INTO ".$this->table." SET `f_tim`='{$f_tim}', `f_ip`='{$f_ip}', `f_browser`='{$f_browser}', `f_action_type`='{$f_action_type}', `f_info`='{$f_info}'";
			$db->Q->query($sql);
		}		
	}
	
	function setUid($uid)
	{		
		$db = $this->registry['db'];
		$uid = $db->Q->real_escape_string($uid);	
		
		$this->uid = $uid;
	}
	
	function init()
	{		
		$db = $this->registry['db'];
		$local = $this->registry['local'];
		
		if ($this->uid>0)
		{	
			$sql = $local->getLocal("ab.s.table.ib_user_log", "");
			
			$this->table = "t_ib_user_".$this->uid."_logs";			
			$create_user_table = str_replace('$table', $this->table, $sql);			
			
			$db->Q->query($create_user_table);
		}
	}
}
?>