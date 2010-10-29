<h1>Welcome to CodeIgniter!</h1>

<p>The page you are looking at is being generated dynamically by CodeIgniter.</p>

<p>If you would like to edit this page you'll find it located at:</p>
<code>system/application/views/welcome_message.php</code>

<p>The corresponding controller for this page is found at:</p>
<code>system/application/controllers/welcome.php</code>

<p>If you are exploring CodeIgniter for the very first time, you should start by reading the <a href="user_guide/">User Guide</a>.</p>

<?php
if ($this->session->userdata('uid') > 0)
{
?>
<p><a href="/logout">退出</a></p>
<?php
}
else
{
	if($this->session->flashdata('error_msg'))
	{
		echo '<b>'.$this->session->flashdata('error_msg').'</b>';
	}
?>
<form action="/login" method="POST">
用户名：<input type="text" name="username" /> <br />
密码：<input type="password" name="password" />
<input type="submit" value="登录" />
</form>
<?php
}
?>
<p><br />Page rendered in {elapsed_time} seconds</p>