 <?php
class DB
{
	private $registry;
	private $vars = array();
	
	function __construct($registry)
	{
		global $abhost, $abun, $abps, $abbs;
		
		$this->registry = $registry;
		
		$this->Q = new mysqli($abhost, $abun, $abps, $abbs); 
		$this->Q->set_charset('cp1251');
	}
	
	function fetch_assoc($rc)
	{
		if($rc === FALSE)
			return array();
		else
			return $rc->fetch_assoc();
	}
}
?>