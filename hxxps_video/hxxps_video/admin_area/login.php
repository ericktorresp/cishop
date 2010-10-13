<?php

/**
 * ClipBucket Login
 */

define('THIS_PAGE', 'ADMIN_LOGIN');
require '../includes/admin_config.php';
Assign('THIS_PAGE', THIS_PAGE);

if($userquery->is_admin_logged_as_user())
{
	$userquery->revert_from_user();
	redirect_to(BASEURL.'/admin_area');
}

if($userquery->admin_login_check(TRUE))
{
	redirect_to(BASEURL."/".ADMINDIR."/index.php");
}
$eh->flush();

$thisurl = $_SERVER['PHP_SELF'];
Assign('THIS_URL', $thisurl);

if(!empty($_REQUEST['returnto']))
{
   $return_to = $_REQUEST['returnto'];
   Assign('return_to',$return_to);
}

if(isset($_POST['login'])){
	$username = $_POST['username'];
	$username = mysql_clean(clean($username));
	$password = mysql_clean(clean($_POST['password']));
	
	//Loggin User
	$userquery->login_user($username,$password);
	
	//Checking if logged in user has access or not
	if(userid())
	{
		if($userquery->login_check('admin_access'))
			redirect_to('index.php');
	}
}

subtitle('Admin Login');
Template('global_header.html');
Template('login.html');
?>