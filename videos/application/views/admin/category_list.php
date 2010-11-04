<?php $this->load->view('admin/header');?>
<h1><?php echo $this->lang->line('category');?></h1>
<?php
if($this->session->flashdata('error'))
{
	echo '<div id="error">'.$this->session->flashdata('error').'</div>';
}
if($this->session->flashdata('infomation'))
{
	echo '<div id="infomation">'.$this->session->flashdata('infomation').'</div>';
}
?>
<p>
	<img src="<?php echo base_url();?>images/icon_add.png" alt="Add new" />
	<?php echo anchor('admin/category/add', $this->lang->line('video_add')); ?>
</p>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#cccccc" width="100%">
	<tr>
		<td bgcolor="#cccccc"><strong>Title</strong></td>
		<td bgcolor="#cccccc" colspan="3"><strong>Operate</strong></td>
	</tr>
<?php
foreach($cats as $cat)
{
?>
	<tr onmouseover="this.bgColor='#dddddd'" onmouseout="this.bgColor='#ffffff'" onclick="parent.location='<?php echo site_url("admin/category/edit/".$cat->cid)?>'" bgcolor="#ffffff">
		<td><?php echo $cat->ctitle;?></td>
		<td width="150"><img src="<?php echo base_url();?>images/icon_images.png" alt="Manage videos" /><a href="<?php echo site_url("admin/video/category/".$cat->cid)?>">Manage Videos</a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_update.png" alt="Edit" /> <a href="<?php echo site_url("admin/category/edit/".$cat->cid)?>">Edit</a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_delete.png" alt="Delete" /><a href="<?php echo site_url("admin/category/delete/".$cat->cid)?>">Delete</a></td>
	</tr>
<?php
}
?>
</table>
<?php $this->load->view('admin/footer');?>