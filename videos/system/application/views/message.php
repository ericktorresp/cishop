<h1><?php echo $message_title?></h1>
<?php
if($link)
{
	echo '<a href="'.$link['url'].'">'.$link['title'].'</a>';
	echo '<script language="javascript">';
	echo 'setTimeout("window.location.href =\''.$link['url'].'\';", 3000);';
	echo '</script>';
}
?>