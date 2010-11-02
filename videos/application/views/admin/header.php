<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Administration</title>
<link rel="stylesheet" type="text/css"
	href="<?php echo base_url();?>css/admin/styles.css" />
<link rel="stylesheet" type="text/css"
	href="<?php echo base_url();?>css/admin/jquery.fancybox.css"
	media="screen" />
<link rel="shortcut icon"
	href="<?php echo base_url();?>images/favicon.ico" type="image/x-icon" />
<script type="text/javascript"
	src="<?php echo base_url();?>js/jquery.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		
	});
</script>
</head>
<body>
<div id="container">
	<div id="header">
		<div id="logo"><img src="<?php echo base_url();?>images/admin/admin_logo.jpg" alt="" /></div>
		<?php if($this->session->userdata('uid')){?>
		<div id="logout"><a href="<?php echo site_url("/logout")?>">Logout</a></div>
		<?php }?>
	</div>
	<div id="navigation">
		<ul>
			<li><a href="<?php echo site_url("admin/video")?>"
			<?php if ($this->uri->segment(2) == "video") echo ' class="active"'; ?>>Manage
			Video</a></li>
			<li><a href="<?php echo site_url("admin/category")?>"
			<?php if ($this->uri->segment(2) == "category") echo ' class="active"'; ?>>Category</a></li>
			<li><a href="<?php echo site_url("admin/server")?>"
			<?php if ($this->uri->segment(2) == "server") echo ' class="active"'; ?>>Server</a></li>
		</ul>
	</div>
	<div id="content">
		<div id="content-box">