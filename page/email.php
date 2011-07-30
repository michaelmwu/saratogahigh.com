<?
/*
	email.php
	Email validation, sending
*/

define('EMAIL_SYNTAX',1);
define('EMAIL_DNS',2);
define('EMAIL_MX',3);
define('EMAIL_SMTP',4);

define('SMTP_TIMEOUT',10);

class email
{
	function is_valid($email,$level = EMAIL_SYNTAX,$return = false)
	{
		$reached = 0;
		list($user,$domain) = explode("@", $email);
		if (!(isset($user) && isset($domain)))
			return 0;
	
		if($level >= EMAIL_SYNTAX)
		{
			$pattern_user = '^([0-9a-z]*([-|_]?[0-9a-z]+)*)(([-|_]?)\.([-|_]?)[0-9a-z]*([-|_]?[0-9a-z]+)+)*([-|_]?)$';
			$pattern_domain = '^([0-9a-z]+([-]?[0-9a-z]+)*)(([-]?)\.([-]?)[0-9a-z]*([-]?[0-9a-z]+)+)*\.[a-z]{2,9}$';
			if(eregi($pattern_user, $user) && eregi($pattern_domain, $domain))
				$reached = EMAIL_SYNTAX;
			else if($return)
				return $reached;
			else
				return false;
		}
		
		if($level >= EMAIL_DNS)
		{
			if(gethostbynamel($domain))
				$reached = EMAIL_DNS;
			else if($return)
				return $reached;
			else
				return false;
		}
		
		if($level >= EMAIL_MX)
		{
			if(dns_get_mx($domain,$mx))
				$reached = EMAIL_MX;
			else if($return)
				return $reached;
			else
				return false;
		}

/*  Borrowed!
*	This script was writed by Setec Astronomy - setec@freemail.it
*
*	This script is distributed  under the GPL License
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* 	GNU General Public License for more details.
*
*	http://www.gnu.org/licenses/gpl.txt
*
*/

		if($level >= EMAIL_SMTP)
		{
			$port = 25;
			$localhost = $_SERVER['HTTP_HOST'];
			$sender = 'info@' . $localhost;
			
			$id = 0;
			while ($id < count ($mx))
			{
				$result = true;
				if ($connection = @fsockopen ($mx[$id], $port, $errno, $error, SMTP_TIMEOUT))
				{
					foreach(array("HELO $localhost\r\n","MAIL FROM:<$sender>\r\n","RCPT TO:<$email>\r\n","data\r\n") as $message)
					{
						fputs ($connection,$message);
						$data = fgets ($connection,1024);
						$response = substr ($data,0,1);
						
						if ($response != '2') // 200, 250 etc.
						{
							$result = false;
							break;
						}
					}
					
					fputs ($connection,"QUIT\r\n"); 
					fclose ($connection);
				}
				else
					$result = false;
				$id++;
				
				if($result)
					break;
			}
			
			if($result)
				$reached = EMAIL_SMTP;
			else if($return)
				return $reached;
			else
				return false;
		}

		if($return)
			return $reached;
		else
			return true;
	}
}
?>
