<?php
Class Controller_Security Extends Controller_Base
{	
	public function index()
	{
		
	}
	
	public function get()
	{		
		$auth = $this->registry['auth'];
		$db = $this->registry['db'];	
		$logger = $this->registry['logger'];
		
		$uid = $db->Q->real_escape_string($_POST['uid']);		
		
		$sql = "SELECT f_security FROM t_ib_users WHERE f_uid = '{$uid}'";	
		
		$rc = $db->Q->query($sql);
		$arr = $db->fetch_assoc($rc);		
		
		$r = $auth->check_session();
		
		if ($r)
		{
			if ($arr['f_security']!='')
				echo $arr['f_security'];
			else
				echo $this->set_default($f_uid);			
		}
	}
	
	public function change_login()
	{		
		$auth = $this->registry['auth'];		
		$db = $this->registry['db'];	
		$logger = $this->registry['logger'];
		$local = $this->registry['local'];
		
		$uid = $db->Q->real_escape_string($_POST['uid']);		
		
		$r = $auth->check_session();

		if ($r)
		{			
			$conf_login_do_my = $db->Q->real_escape_string($_POST['conf_login_do_my']);			
			$conf_login_suffix = $db->Q->real_escape_string($_POST['conf_login_suffix']);		
			$conf_login_suffix_skip_sms = $db->Q->real_escape_string($_POST['conf_login_suffix_skip_sms']);
			
			$conf_login_my = $db->Q->real_escape_string($_POST['conf_login_my']);
			$conf_login_my2 = $db->Q->real_escape_string($_POST['conf_login_my2']);
			
			if ($conf_login_do_my!=1)
			{
				$sql = "SELECT * FROM t_ib_users WHERE f_uid = '{$uid}'";
				$rc = $db->Q->query($sql);
				$arr = $db->fetch_assoc($rc);
			
				if ($arr['f_ownerName']!='')				
					$new_login = $this->generate_login($arr['f_ownerName']).$conf_login_suffix;
			}
			
			if ($conf_login_do_my==1)
			{
				if ($conf_login_my==$conf_login_my2)
				{
					if (strlen($conf_login_my)>=6)
					{
						$sql = "SELECT * FROM t_ib_users WHERE f_login = '{$conf_login_my}'";
						$rc = $db->Q->query($sql);
						$arr = $db->fetch_assoc($rc);
					
						if ($arr['f_login']=='')
							$new_login = $conf_login_my;
						else				
							$response['error'] = $local->getLocal("user_found");
					}
					else
						$response['error'] = $local->getLocal("user_login_len_less_6");
				}
				else
					$response['error'] = $local->getLocal("user_logins_not_equal");				
			}
			
			if ($new_login!='')
			{
				$sql = "UPDATE t_ib_users SET f_login = '{$new_login}' WHERE f_uid='{$uid}'";
				$rc = $db->Q->query($sql);
				
				$logger->write($uid, "310");
			}
			
			echo json_encode($response);
		}
	}
	
	public function change_pay_password($type="pay_password")
	{
		$auth = $this->registry['auth'];
		$db = $this->registry['db'];	
		$logger = $this->registry['logger'];
		$local = $this->registry['local'];
		$abmagic = $this->registry['abmagic'];		
		
		$uid = $db->Q->real_escape_string($_POST['uid']);	
		
		$conf_word_old = $db->Q->real_escape_string($_POST['conf_word_old']);
		$conf_word_new = $db->Q->real_escape_string($_POST['conf_word_new']);
		$conf_word_new2 = $db->Q->real_escape_string($_POST['conf_word_new2']);
		
		$conf_word_old = hash("sha512", $conf_word_old.$abmagic[0]);
		
		if ($type=='pay_password')
		{
			$change_field = "f_pay_password";
			$error_field = "user_pay_password";
		}
		
		if ($type=='magic_word')
		{
			$change_field = "f_word";
			$error_field = "user_word";
		}
		
		if ($conf_word_old!='')
		{			
			$sql = "SELECT f_id FROM t_ib_users WHERE f_uid = '{$uid}' AND {$change_field} = '{$conf_word_old}'";
			$rc = $db->Q->query($sql);
			$arr = $db->fetch_assoc($rc);			
			
			if ($arr['f_id']!='')
			{				
				if ($conf_word_new==$conf_word_new2)
				{					
					if (strlen($conf_word_new)>=6)
					{
						$new_pay_password = hash("sha512", $conf_word_new.$abmagic[0]);
						
						$sql = "UPDATE t_ib_users SET {$change_field} = '{$new_pay_password}' WHERE f_uid = '{$uid}' AND {$change_field} = '{$conf_word_old}'";
						$rc = $db->Q->query($sql);
						
						if ($type=='magic_word') $logger->write($uid, "300");
						if ($type=='pay_password') $logger->write($uid, "320");
					}
					else
						$response['error'] = $local->getLocal("{$error_field}_len_less_6");
				}
				else
					$response['error'] = $local->getLocal("{$error_field}_not_equal");
			}
			else
				$response['error'] = $local->getLocal("{$error_field}_incorrect");
		}
		
		echo json_encode($response);
		
	}
	
	public function change_magic_word()
	{
		$this->change_pay_password("magic_word");
	}
	
	public function set()
	{
		$auth = $this->registry['auth'];
		$db = $this->registry['db'];	
		$logger = $this->registry['logger'];
		
		$uid = $db->Q->real_escape_string($_POST['uid']);
		$data = $db->Q->real_escape_string($_POST['data']);
		
		$r = $auth->check_session();
		
		if ($r)
		{
			$sql = "UPDATE t_ib_users SET f_security = '{$data}' WHERE f_uid='{$uid}'";			
			$rc = $db->Q->query($sql);
			
			$logger->write($uid, "350");
		}
	}
	
	private function set_default($f_uid)
	{
		$db = $this->registry['db'];
		$logger = $this->registry['logger'];
		
		$arr = array(
			"mailPwd" => 0,
			"mailBack" => 0,
		
			"lim1" => 0,
			"limPwd1" => 0,
			"limBack1" => 0,
		
			"lim2" => 0,
			"limPwd2" => 0,
			"limBack2" => 0,
		
			"limPwd3" => 0,
			"limBack3" => 0,		
		
			"viewPwd" => 0,
			"viewBack" => 0,

			"selfPwd" => 0,
			"currencyPwd" => 0
		);
		
		$f_security = json_encode($arr);
		
		$sql = "UPDATE t_ib_users SET f_security = '{$f_security}' WHERE f_uid = '{$f_uid}'";
		$rc = $db->Q->query($sql);
		
		return $f_security;
	}
	
	private function generate_login($ownerName)
	{
		$translit = $this->rus2translit($ownerName);
				
		$exp = explode(" ", $translit);
		
		$s1 = substr($exp[1], 0, 1);
		$s2 = substr($exp[0], 0, 5);
		
		$i1 = rand(0, 9);
		$i2 = rand(0, 9);
		$i3 = rand(0, 9);
		$i4 = rand(0, 9);
		
		return $i1.$s1.$s2.$i2.$i3.$i4;
	}	
	
	private function rus2translit($text)
	{		
		$rus_alphabet = array(
			'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
			'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
			'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
			'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
			'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
			'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
		);    
		
		$rus_alphabet_translit = array(
			'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'I',
			'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
			'H', 'C', 'CH', 'SH', 'SH', '`', 'Y', '`', 'E', 'IU', 'IA',
			'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'i',
			'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
			'h', 'c', 'ch', 'sh', 'sh', '`', 'y', '`', 'e', 'iu', 'ia'
		);
    
		$translit = str_replace($rus_alphabet, $rus_alphabet_translit, $text);
		
		return strtolower($translit);
	}
}