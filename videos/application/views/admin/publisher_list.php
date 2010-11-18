<?php $this->load->view('admin/header');?>
<h1><?php echo lang('publisher');?></h1>
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
	<?php echo anchor('admin/publisher/add', lang('add')); ?>
</p>
<?php echo $pagination;?>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#cccccc" width="100%">
	<tr>
		<td bgcolor="#cccccc"><strong><?php echo lang('name')?></strong></td>
		<td bgcolor="#cccccc"><strong><?php echo lang('nationality')?></strong></td>
		<td bgcolor="#cccccc" colspan="3"><strong><?php echo lang('operation')?></strong></td>
	</tr>
<?php
foreach($publishers as $publisher)
{
?>
	<tr onmouseover="this.bgColor='#dddddd'" onmouseout="this.bgColor='#ffffff'" onclick="parent.location='<?php echo site_url("admin/publisher/edit/".$publisher->id)?>'" bgcolor="#ffffff">
		<td><?php echo $publisher->name;?></td>
		<td><?php echo lang($publisher->nationality);?></td>
		<td width="150"><img src="<?php echo base_url();?>images/icon_images.png" alt="<?php echo lang('videos');?>" /><a href="<?php echo site_url("admin/publisher/videos/".$publisher->id)?>"><?php echo lang('videos');?></a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_update.png" alt="<?php echo lang('edit');?>" /> <a href="<?php echo site_url("admin/publisher/edit/".$publisher->id)?>"><?php echo lang('edit');?></a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_delete.png" alt="<?php echo lang('delete');?>" /><a href="<?php echo site_url("admin/publisher/delete/".$publisher->id)?>"><?php echo lang('delete');?></a></td>
	</tr>
<?php
}
?>
</table>
<?php echo $pagination;?>
<?php $this->load->view('admin/footer');?>