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
	<?php echo anchor('admin/category/add', $this->lang->line('add')); ?>
</p>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#cccccc" width="100%">
	<tr>
		<td bgcolor="#cccccc"><strong><?php echo $this->lang->line('video_title')?></strong></td>
		<td bgcolor="#cccccc" colspan="3"><strong><?php echo $this->lang->line('operation')?></strong></td>
	</tr>
<?php
foreach($cats as $cat)
{
?>
	<tr onmouseover="this.bgColor='#dddddd'" onmouseout="this.bgColor='#ffffff'" onclick="parent.location='<?php echo site_url("admin/category/edit/".$cat->cid)?>'" bgcolor="#ffffff">
		<td><?php echo $cat->ctitle;?></td>
		<td width="150"><img src="<?php echo base_url();?>images/icon_images.png" alt="<?php echo $this->lang->line('videos');?>" /><a href="<?php echo site_url("admin/video/category/".$cat->cid)?>"><?php echo $this->lang->line('videos');?></a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_update.png" alt="<?php echo $this->lang->line('edit');?>" /> <a href="<?php echo site_url("admin/category/edit/".$cat->cid)?>"><?php echo $this->lang->line('edit');?></a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_delete.png" alt="<?php echo $this->lang->line('delete');?>" /><a href="<?php echo site_url("admin/category/delete/".$cat->cid)?>"><?php echo $this->lang->line('delete');?></a></td>
	</tr>
<?php
}
?>
</table>
<?php $this->load->view('admin/footer');?>