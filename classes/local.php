 <?php
class Local
{
	private $registry;
	private $vars = array();
	
	function __construct($registry)
	{
		$this->registry = $registry;
	}
	
	function getLocal($name, $type='error')
	{
		$db = $this->registry['db'];		
		$name = $db->Q->real_escape_string($name);
		
		switch($type)
		{
			case "error":
				$local_prefix = "ab.ib.error";
				break;
		}
		
		if ($local_prefix!='')
			$local_prefix.= ".";
		
		$sql = "SELECT f_v FROM t_local WHERE f_key = '{$local_prefix}{$name}'";
		$rc = $db->Q->query($sql);
		$arr = $db->fetch_assoc($rc);
		
		return base64_decode($arr['f_v']);
	}
}
?>