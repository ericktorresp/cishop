<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>GalleryCMS | Administration</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/admin/styles.css" />
<link rel="shortcut icon" href="<?php echo base_url();?>images/favicon.ico" type="image/x-icon" />
</head>
<body>
	<div id="container">
		<div id="header">
			<div id="logo"><img src="<?php echo base_url();?>images/admin_logo.jpg" alt="" /></div>
		</div>
		<div id="content">
			<div id="content-box">
				<div id="login-box">
					<form action="/login" method="post" name="login">
						<h1>Login</h1>
						<div>
							<span>Username:<br /></span>
							<input type="text" name="username" id="username" value="" style="width:200px;" />
						</div>
						<div>
							<span>Password:<br /></span>
							<input type="password" name="password" id="password" value="" style="width:200px;" />
						</div>
						<div>
							<input type="submit" value="Login" id="submit" name="loginsubmit" />
						</div>
						<div></div>
					</form>
				</div>
			</div>
		</div>
	</div>
</body>
</html>