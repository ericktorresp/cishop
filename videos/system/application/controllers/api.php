<?php
define('UC_CLIENT_VERSION', '1.5.2');	//note UCenter 版本标识
define('UC_CLIENT_RELEASE', '20101001');

define('API_DELETEUSER', 1);		//note 用户删除 API 接口开关
define('API_RENAMEUSER', 1);		//note 用户改名 API 接口开关
define('API_GETTAG', 1);		//note 获取标签 API 接口开关
define('API_SYNLOGIN', 1);		//note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1);		//note 同步登出 API 接口开关
define('API_UPDATEPW', 1);		//note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1);	//note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1);		//note 更新域名解析缓存 开关
define('API_UPDATEAPPS', 1);		//note 更新应用列表 开关
define('API_UPDATECLIENT', 1);		//note 更新客户端缓存 开关
define('API_UPDATECREDIT', 1);		//note 更新用户积分 开关
define('API_GETCREDITSETTINGS', 1);	//note 向 UCenter 提供积分设置 开关
define('API_GETCREDIT', 1);		//note 获取用户的某项积分 开关
define('API_UPDATECREDITSETTINGS', 1);	//note 更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

//error_reporting(0);
set_magic_quotes_runtime(0);

defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

class Api extends Controller
{
    var $users_model;
 
    function __construct()
    {
    	$this->Api();
    }
    
	function Api()
	{
		parent::Controller();
		$this->load->model('membersmodel');
	}
	
	function index()
	{
	    
	}
	
	function uc()
	{
//	    parse_str($this->input->server('QUERY_STRING'), $_GET);
	    $_DCACHE = $get = $post = array();
		$code = $this->input->get('code');
		parse_str($this->ucenter->authcode($code, 'DECODE', UC_KEY), $get);
		if(MAGIC_QUOTES_GPC)
		{
			$get = _stripslashes($get);
		}
//		var_dump($get);die;
		$timestamp = time();
		if($timestamp - $get['time'] > 3600)
		{
			exit('Authracation has expiried');
		}
		if(empty($get))
		{
			exit('Invalid Request');
		}
		$action = $get['action'];
		require_once FCPATH.'./uc_client/lib/xml.class.php';
		$post = xml_unserialize(file_get_contents('php://input'));

		if(in_array($action, array(
		'test',
		'deleteuser',
		'renameuser',
		'gettag',
		'synlogin',
		'synlogout',
		'updatepw',
		'updatebadwords',
		'updatehosts',
		'updateapps',
		'updateclient',
		'updatecredit',
		'getcreditsettings',
		'updatecreditsettings')))
		{
			$uc_note = new uc_note();
			exit($uc_note->$get['action']($get, $post));
		}
		else
		{
			exit(API_RETURN_FAILED);
		}
	}
}

class uc_note
{
	var $dbconfig = '';
	var $db = '';
	var $tablepre = '';
	var $appdir = '';
	var $CI;

	function _serialize($arr, $htmlon = 0)
	{
		if(!function_exists('xml_serialize')) {
			include_once FCPATH.'./uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note()
	{
	    $this->appdir = FCPATH;
	    $this->CI = & get_instance();
	}

	function test($get, $post)
	{
		return API_RETURN_SUCCEED;
	}

	function deleteuser($get, $post)
	{
		$uids = $get['ids'];
		!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

		return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post)
	{
		$uid = $get['uid'];
		$usernameold = $get['oldusername'];
		$usernamenew = $get['newusername'];
		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}

		return API_RETURN_SUCCEED;
	}

	function gettag($get, $post)
	{
		$name = $get['id'];
		if(!API_GETTAG) {
			return API_RETURN_FORBIDDEN;
		}
		
		$return = array();
		return $this->_serialize($return, 1);
	}

	function synlogin($get, $post)
	{
		$uid = $get['uid'];
		$username = $get['username'];
		if(!API_SYNLOGIN) {
		    return API_RETURN_FORBIDDEN;
		}
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$cookietime = 2592000;
		$_auth_key = $this->CI->ucenter->user_key;
		
		$user = $this->CI->membersmodel->get_user($uid);
		if($user)
		{
			$this->CI->session->set_userdata('uid', $uid);
		}
	}

	function synlogout($get, $post)
	{
		if(!API_SYNLOGOUT)
		{
			return API_RETURN_FORBIDDEN;
		}
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$this->CI->session->sess_destroy();
	}

	function updatepw($get, $post)
	{
		if(!API_UPDATEPW)
		{
			return API_RETURN_FORBIDDEN;
		}
		$username = $get['username'];
		$password = $get['password'];
		
		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post)
	{
		if(!API_UPDATEBADWORDS)
		{
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$data = array();
		if(is_array($post))
		{
			foreach($post as $k => $v)
			{
				$data['findpattern'][$k] = $v['findpattern'];
				$data['replace'][$k] = $v['replacement'];
			}
		}
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post)
	{
		if(!API_UPDATEHOSTS)
		{
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post)
	{
		if(!API_UPDATEAPPS)
		{
			return API_RETURN_FORBIDDEN;
		}
		$UC_API = $post['UC_API'];

		$cachefile = $this->appdir.'./uc_client/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		//note 写配置文件
		if(is_writeable($this->appdir.'./config.inc.php'))
		{
			$configfile = trim(file_get_contents($this->appdir.'./config.inc.php'));
			$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
			$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$UC_API');", $configfile);
			$fp = @fopen($this->appdir.'./config.inc.php', 'w');
			if($fp)
			{
				@fwrite($fp, trim($configfile));
				@fclose($fp);
			}
		}
	
		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post)
	{
		if(!API_UPDATECLIENT)
		{
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updatecredit($get, $post)
	{
		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		$credit = $get['credit'];
		$amount = $get['amount'];
		$uid = $get['uid'];
		return API_RETURN_SUCCEED;
	}

	function getcredit($get, $post)
	{
		if(!API_GETCREDIT)
		{
			return API_RETURN_FORBIDDEN;
		}
	}

	function getcreditsettings($get, $post)
	{
		if(!API_GETCREDITSETTINGS)
		{
			return API_RETURN_FORBIDDEN;
		}
		$credits = array();
		return $this->_serialize($credits);
	}

	function updatecreditsettings($get, $post)
	{
		if(!API_UPDATECREDITSETTINGS)
		{
			return API_RETURN_FORBIDDEN;
		}
		return API_RETURN_SUCCEED;
	}
}

function _setcookie($var, $value, $life = 0, $prefix = 1)
{
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	setcookie(($prefix ? $cookiepre : '').$var, $value,
		$life ? $timestamp + $life : 0, $cookiepath,
		$cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function _stripslashes($string)
{
	if(is_array($string))
	{
		foreach($string as $key => $val)
		{
			$string[$key] = _stripslashes($val);
		}
	}
	else
	{
		$string = stripslashes($string);
	}
	return $string;
}