 <?php
class Auth
{
	private $registry;
	private $vars = array();
	
	function __construct($registry)
	{
		$this->registry = $registry;
	}
	
	function user_hash($uid)
	{		
		$r[] = date("d.m.Y");
		$r[] = $_SERVER['HTTP_HOST'];
		$r[] = $_SERVER['HTTP_X_REAL_IP'];
		$r[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
		$r[] = $_SERVER['HTTP_ACCEPT'];
		$r[] = $_SERVER['HTTP_USER_AGENT'];
		$r[] = $_SERVER['HTTP_ACCEPT_ENCODING'];
		$r[] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$r[] = $this->get_guid_server();
		$r[] = $uid;		
		
		foreach($r as $value)		
			$str[] = hash("sha512", $value);
		
		$hash = hash("sha512", implode("", $str));		
		return hash("sha512", $hash);
	}
	
	function get_guid_server()
	{
		$key = "7398464C-023B-11E6-B52A-94EA9333BFBE";
		return $key;
	}
	
	function check_session()
	{
		// if ($_POST['GUIDSRV']!=$this->get_guid_server()) exit;
		
		$db = $this->registry['db'];		
		
		$uid = $db->Q->real_escape_string($_POST['uid']);
		$key = $db->Q->real_escape_string($_POST['key']);

		$f_key = $this->user_hash($uid);
		
		$sql = "SELECT f_key FROM t_ses WHERE f_key = '{$f_key}' AND f_str = '{$key}'";
		$rc = $db->Q->query($sql);
		$arr = $db->fetch_assoc($rc);
		
		if ($arr['f_key']!='')
			return true;
		else
			return false;
	}
}
?>